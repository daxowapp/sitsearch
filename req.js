const https = require('https');

https.get('https://search.studyinturkiye.com/results/?search=Bahcesehir', (res) => {
  let data = '';
  res.on('data', (chunk) => data += chunk);
  res.on('end', () => {
    // Look for ProgramBoxUni cards
    const lines = data.split('\n');
    lines.forEach(line => {
      if (line.includes('ProgramBoxUni-title') || line.includes('Bahcesehir')) {
        console.log(line.trim());
      }
    });
  });
}).on('error', (e) => {
  console.error(e);
});
