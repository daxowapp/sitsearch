#!/usr/bin/env node

/**
 * GEO FAQ Generator — Batch FAQ generation for university programs
 *
 * Uses OpenRouter API with round-robin key rotation to generate
 * 20 FAQs per program for Generative Engine Optimization (GEO).
 *
 * Usage:
 *   node generate-faqs.js                  # Process all pending programs
 *   node generate-faqs.js --limit 10       # Process only 10 programs
 *   node generate-faqs.js --post-id 12345  # Process a single program
 *   node generate-faqs.js --concurrency 3  # Run 3 parallel requests
 *
 * Environment Variables:
 *   OPENROUTER_KEYS   - Comma-separated OpenRouter API keys
 *   DB_HOST           - MySQL host (default: localhost)
 *   DB_USER           - MySQL user (default: root)
 *   DB_PASSWORD       - MySQL password (default: root)
 *   DB_NAME           - MySQL database name (default: wordpress)
 *   DB_PREFIX         - WordPress table prefix (default: wp_)
 *   MODEL             - OpenRouter model (default: google/gemini-2.0-flash)
 */

const mysql = require('mysql2/promise');

// =============================
// Configuration
// =============================
const CONFIG = {
  openrouter: {
    keys: (process.env.OPENROUTER_KEYS || '').split(',').filter(Boolean),
    model: process.env.MODEL || 'google/gemini-2.0-flash',
    baseUrl: 'https://openrouter.ai/api/v1/chat/completions',
  },
  db: {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || 'root',
    database: process.env.DB_NAME || 'wordpress',
    prefix: process.env.DB_PREFIX || 'wp_',
  },
  concurrency: parseInt(process.env.CONCURRENCY || '2', 10),
  retryAttempts: 3,
  retryDelay: 5000,
  rateLimitDelay: 1500, // ms between requests per key
};

// Parse CLI args
const args = process.argv.slice(2);
let limit = 0;
let singlePostId = null;
for (let i = 0; i < args.length; i++) {
  if (args[i] === '--limit' && args[i + 1]) limit = parseInt(args[i + 1], 10);
  if (args[i] === '--post-id' && args[i + 1]) singlePostId = parseInt(args[i + 1], 10);
  if (args[i] === '--concurrency' && args[i + 1]) CONFIG.concurrency = parseInt(args[i + 1], 10);
}

// =============================
// Round-Robin Key Manager
// =============================
class KeyManager {
  constructor(keys) {
    this.keys = keys;
    this.index = 0;
    this.lastUsed = new Map();
  }

  async getKey() {
    if (this.keys.length === 0) throw new Error('No OpenRouter API keys provided');

    const key = this.keys[this.index % this.keys.length];
    this.index++;

    // Rate limit per key
    const lastTime = this.lastUsed.get(key) || 0;
    const elapsed = Date.now() - lastTime;
    if (elapsed < CONFIG.rateLimitDelay) {
      await sleep(CONFIG.rateLimitDelay - elapsed);
    }

    this.lastUsed.set(key, Date.now());
    return key;
  }
}

// =============================
// FAQ Categories & Prompt
// =============================
const FAQ_CATEGORIES = [
  { id: 'tuition', label: 'Tuition fees, payment plans, scholarships, financial aid' },
  { id: 'admission', label: 'Entry requirements, application process, documents, language tests' },
  { id: 'academic', label: 'Curriculum, courses, thesis, career prospects after graduation' },
  { id: 'campus_life', label: 'Student life, accommodation, facilities, city life' },
  { id: 'visa', label: 'Student visa, residence permit, health insurance, travel' },
];

function buildPrompt(program) {
  const uniName = program.university || 'the university';
  let prompt = `Generate exactly 20 FAQs for the following university program. Organize them into 5 categories with exactly 4 questions each.\n\n`;
  prompt += `## Program Data\n`;
  prompt += `- Program: ${program.title}\n`;
  prompt += `- University: ${uniName}\n`;
  if (program.country) prompt += `- Country: ${program.country}\n`;
  if (program.city) prompt += `- City: ${program.city}\n`;
  if (program.degree) prompt += `- Degree Level: ${program.degree}\n`;
  if (program.language) prompt += `- Language of Instruction: ${program.language}\n`;
  if (program.faculty) prompt += `- Faculty: ${program.faculty}\n`;
  if (program.fee) prompt += `- Annual Tuition Fee: ${program.currency || 'USD'} ${program.fee}\n`;
  if (program.discounted_fee) prompt += `- Discounted Fee: ${program.currency || 'USD'} ${program.discounted_fee}\n`;
  if (program.duration) prompt += `- Duration: ${program.duration} years\n`;
  if (program.ielts) prompt += `- IELTS Requirement: ${program.ielts}\n`;
  if (program.toefl) prompt += `- TOEFL Requirement: ${program.toefl}\n`;
  if (program.ranking) prompt += `- QS World Ranking: ${program.ranking}\n`;
  if (program.students) prompt += `- Number of Students: ${program.students}\n`;
  if (program.year_founded) prompt += `- Year Founded: ${program.year_founded}\n`;
  if (program.description) prompt += `- Description: ${program.description.substring(0, 500)}\n`;
  if (program.curriculum) prompt += `- Curriculum Subjects: ${program.curriculum}\n`;

  prompt += `\n## Categories (4 questions each)\n`;
  FAQ_CATEGORIES.forEach((cat, i) => {
    prompt += `${i + 1}. ${cat.id} - ${cat.label}\n`;
  });

  prompt += `\n## CRITICAL Rules\n`;
  prompt += `- EVERY question MUST include the university name "${uniName}" — e.g. "What is the tuition fee for the ${program.title} program at ${uniName}?"\n`;
  prompt += `- EVERY answer MUST mention "${uniName}" at least once\n`;
  prompt += `- Each answer MUST be 2-4 sentences long\n`;
  prompt += `- Include specific data from program info (fees, scores, etc.) when relevant\n`;
  prompt += `- Use entity-rich language (mention university name, city, country explicitly)\n`;
  prompt += `- Do NOT hallucinate data — if you don't have specific info, give general but accurate answers about studying in ${program.country || 'Turkey'}\n`;
  prompt += `- Write in a clear, factual, citation-worthy style optimized for AI search engine citation\n`;
  prompt += `- Questions should be natural, conversational, and varied\n`;

  prompt += `\n## Response Format (JSON)\n`;
  prompt += `{"faqs": [{"question": "...", "answer": "...", "category": "tuition"}, ...]}`;

  return prompt;
}

// =============================
// Database Operations
// =============================
async function getPendingPrograms(pool, prefix, limitCount, postId) {
  const faqTable = `${prefix}program_faqs`;
  const postsTable = `${prefix}posts`;
  const metaTable = `${prefix}postmeta`;
  const termRelTable = `${prefix}term_relationships`;
  const termTaxTable = `${prefix}term_taxonomy`;
  const termsTable = `${prefix}terms`;

  let query;
  let params = [];

  if (postId) {
    query = `SELECT ID FROM ${postsTable} WHERE ID = ? AND post_type = 'sit-program' AND post_status = 'publish'`;
    params = [postId];
  } else {
    query = `
      SELECT p.ID
      FROM ${postsTable} p
      LEFT JOIN (
        SELECT post_id, COUNT(*) as faq_count
        FROM ${faqTable}
        GROUP BY post_id
      ) f ON p.ID = f.post_id
      WHERE p.post_type = 'sit-program'
        AND p.post_status = 'publish'
        AND (f.faq_count IS NULL OR f.faq_count < 20)
      ORDER BY p.ID ASC
    `;
    if (limitCount > 0) {
      query += ` LIMIT ?`;
      params = [limitCount];
    }
  }

  const [rows] = await pool.execute(query, params);
  const programIds = rows.map(r => r.ID);

  // Fetch full program data
  const programs = [];
  for (const id of programIds) {
    const program = await getProgramData(pool, prefix, id);
    if (program) programs.push(program);
  }

  return programs;
}

async function getProgramData(pool, prefix, postId) {
  const metaTable = `${prefix}postmeta`;
  const postsTable = `${prefix}posts`;

  // Get post title
  const [postRows] = await pool.execute(
    `SELECT post_title FROM ${postsTable} WHERE ID = ?`,
    [postId]
  );
  if (!postRows.length) return null;

  // Get meta values
  const metaKeys = [
    'zh_university', 'Official_Tuition', 'Discounted_Tuition', 'Tuition_Currency',
    'Study_Years', 'IELTS', 'TOEFL', 'Description', 'Curriculums'
  ];
  const [metaRows] = await pool.execute(
    `SELECT meta_key, meta_value FROM ${metaTable} WHERE post_id = ? AND meta_key IN (${metaKeys.map(() => '?').join(',')})`,
    [postId, ...metaKeys]
  );

  const meta = {};
  metaRows.forEach(r => { meta[r.meta_key] = r.meta_value; });

  // Get university data
  const uniId = meta['zh_university'];
  let uniName = '', ranking = '', students = '', yearFounded = '';
  if (uniId) {
    const [uniRows] = await pool.execute(
      `SELECT post_title FROM ${postsTable} WHERE ID = ?`,
      [uniId]
    );
    uniName = uniRows[0]?.post_title || '';

    const [uniMeta] = await pool.execute(
      `SELECT meta_key, meta_value FROM ${metaTable} WHERE post_id = ? AND meta_key IN ('QS_Rank', 'Number_Of_Students', 'Year_Founded')`,
      [uniId]
    );
    uniMeta.forEach(r => {
      if (r.meta_key === 'QS_Rank') ranking = r.meta_value;
      if (r.meta_key === 'Number_Of_Students') students = r.meta_value;
      if (r.meta_key === 'Year_Founded') yearFounded = r.meta_value;
    });
  }

  // Get taxonomy terms
  const terms = await getTaxTerms(pool, prefix, postId, uniId);

  return {
    id: postId,
    title: postRows[0].post_title,
    university: uniName,
    country: terms.country,
    city: terms.city,
    degree: terms.degree,
    language: terms.language,
    faculty: terms.faculty,
    fee: meta['Official_Tuition'],
    discounted_fee: meta['Discounted_Tuition'],
    currency: meta['Tuition_Currency'] || 'USD',
    duration: meta['Study_Years'],
    ielts: meta['IELTS'],
    toefl: meta['TOEFL'],
    description: meta['Description'] ? meta['Description'].replace(/<[^>]+>/g, '') : '',
    curriculum: meta['Curriculums'],
    ranking,
    students,
    year_founded: yearFounded,
  };
}

async function getTaxTerms(pool, prefix, postId, uniId) {
  const termRelTable = `${prefix}term_relationships`;
  const termTaxTable = `${prefix}term_taxonomy`;
  const termsTable = `${prefix}terms`;

  const taxMap = {
    'sit-country': 'country',
    'sit-degree': 'degree',
    'sit-language': 'language',
    'sit-faculty': 'faculty',
  };

  const result = { country: '', city: '', degree: '', language: '', faculty: '' };

  for (const [taxonomy, key] of Object.entries(taxMap)) {
    const [rows] = await pool.execute(
      `SELECT t.name FROM ${termsTable} t
       JOIN ${termTaxTable} tt ON t.term_id = tt.term_id
       JOIN ${termRelTable} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
       WHERE tr.object_id = ? AND tt.taxonomy = ? LIMIT 1`,
      [postId, taxonomy]
    );
    result[key] = rows[0]?.name || '';
  }

  // City is on university
  if (uniId) {
    const [rows] = await pool.execute(
      `SELECT t.name FROM ${termsTable} t
       JOIN ${termTaxTable} tt ON t.term_id = tt.term_id
       JOIN ${termRelTable} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
       WHERE tr.object_id = ? AND tt.taxonomy = 'sit-city' LIMIT 1`,
      [uniId]
    );
    result.city = rows[0]?.name || '';
  }

  return result;
}

async function saveFaqs(pool, prefix, postId, faqs, model) {
  const table = `${prefix}program_faqs`;

  // Delete existing FAQs for this program (idempotent)
  await pool.execute(`DELETE FROM ${table} WHERE post_id = ?`, [postId]);

  // Insert new FAQs
  let order = 1;
  for (const faq of faqs) {
    if (!faq.question || !faq.answer || !faq.category) continue;

    await pool.execute(
      `INSERT INTO ${table} (post_id, faq_order, question, answer, category, generated_at, model) VALUES (?, ?, ?, ?, ?, NOW(), ?)`,
      [postId, order, faq.question, faq.answer, faq.category, model]
    );
    order++;
  }

  return order - 1;
}

// =============================
// OpenRouter API Call
// =============================
async function generateFaqs(program, apiKey, model) {
  const prompt = buildPrompt(program);

  const response = await fetch(CONFIG.openrouter.baseUrl, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${apiKey}`,
      'Content-Type': 'application/json',
      'HTTP-Referer': 'https://studyinturkiye.com',
      'X-Title': 'Study in Turkiye GEO FAQ Generator',
    },
    body: JSON.stringify({
      model: model,
      messages: [
        {
          role: 'system',
          content: 'You are an expert education content writer specializing in creating FAQ content for university programs for international students. Your answers must be factual, entity-rich, and optimized for AI search engine citation. Always respond with valid JSON only.',
        },
        { role: 'user', content: prompt },
      ],
      temperature: 0.7,
      max_tokens: 4000,
      response_format: { type: 'json_object' },
    }),
  });

  if (!response.ok) {
    const err = await response.text();
    throw new Error(`OpenRouter API error (${response.status}): ${err}`);
  }

  const data = await response.json();
  const content = data.choices?.[0]?.message?.content ?? '';

  // Parse JSON (may be wrapped in markdown code blocks)
  let cleaned = content.trim();
  if (cleaned.startsWith('```')) {
    cleaned = cleaned.replace(/^```json?\n?/, '').replace(/\n?```$/, '');
  }

  const parsed = JSON.parse(cleaned);
  if (!parsed.faqs || !Array.isArray(parsed.faqs)) {
    throw new Error('Invalid response format: missing "faqs" array');
  }

  return parsed.faqs;
}

// =============================
// Main Worker
// =============================
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function processProgram(pool, prefix, program, keyManager, model, stats) {
  for (let attempt = 1; attempt <= CONFIG.retryAttempts; attempt++) {
    try {
      const apiKey = await keyManager.getKey();
      const faqs = await generateFaqs(program, apiKey, model);
      const saved = await saveFaqs(pool, prefix, program.id, faqs, model);

      stats.success++;
      stats.totalFaqs += saved;
      console.log(`  ✓ [${stats.success + stats.failed}/${stats.total}] ${program.title} — ${saved} FAQs saved`);
      return;
    } catch (err) {
      if (attempt < CONFIG.retryAttempts) {
        console.log(`  ⟳ [Retry ${attempt}/${CONFIG.retryAttempts}] ${program.title}: ${err.message}`);
        await sleep(CONFIG.retryDelay * attempt);
      } else {
        stats.failed++;
        console.error(`  ✗ [FAILED] ${program.title}: ${err.message}`);
      }
    }
  }
}

async function main() {
  console.log('═══════════════════════════════════════════');
  console.log('  GEO FAQ Generator — Study in Türkiye');
  console.log('═══════════════════════════════════════════');

  if (CONFIG.openrouter.keys.length === 0) {
    console.error('ERROR: Set OPENROUTER_KEYS environment variable (comma-separated)');
    process.exit(1);
  }

  console.log(`Keys: ${CONFIG.openrouter.keys.length} | Model: ${CONFIG.openrouter.model} | Concurrency: ${CONFIG.concurrency}`);

  const pool = mysql.createPool({
    host: CONFIG.db.host,
    user: CONFIG.db.user,
    password: CONFIG.db.password,
    database: CONFIG.db.database,
    waitForConnections: true,
    connectionLimit: 10,
  });

  try {
    // Test DB connection
    await pool.execute('SELECT 1');
    console.log('✓ Database connected');

    // Get pending programs
    const programs = await getPendingPrograms(pool, CONFIG.db.prefix, limit, singlePostId);
    console.log(`\nFound ${programs.length} programs to process\n`);

    if (programs.length === 0) {
      console.log('All programs already have FAQs. Nothing to do.');
      return;
    }

    const keyManager = new KeyManager(CONFIG.openrouter.keys);
    const stats = { success: 0, failed: 0, totalFaqs: 0, total: programs.length };
    const startTime = Date.now();

    // Process in batches with concurrency
    for (let i = 0; i < programs.length; i += CONFIG.concurrency) {
      const batch = programs.slice(i, i + CONFIG.concurrency);
      await Promise.all(
        batch.map(program =>
          processProgram(pool, CONFIG.db.prefix, program, keyManager, CONFIG.openrouter.model, stats)
        )
      );
    }

    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
    console.log('\n═══════════════════════════════════════════');
    console.log(`  DONE in ${elapsed}s`);
    console.log(`  ✓ Success: ${stats.success} | ✗ Failed: ${stats.failed}`);
    console.log(`  📝 Total FAQs generated: ${stats.totalFaqs}`);
    console.log('═══════════════════════════════════════════');
  } finally {
    await pool.end();
  }
}

main().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});
