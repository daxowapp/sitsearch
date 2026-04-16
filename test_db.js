const mysql = require('mysql2/promise');

async function check() {
  const connection = await mysql.createConnection({
    host: '127.0.0.1',
    port: 9998,
    user: 'root',
    password: 'root',
    database: 'newsearch'
  });

  const [rows] = await connection.execute(
    "SELECT p.ID as program_id, p.post_title, pm.meta_value as zh_university FROM wp_posts p JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'sit-program' AND p.post_title LIKE '%Bahce%' AND pm.meta_key = 'zh_university' LIMIT 5"
  );
  console.log("Programs:");
  console.log(rows);
  
  const [unis] = await connection.execute(
    "SELECT ID, post_title FROM wp_posts WHERE post_type = 'sit-university' AND post_title LIKE '%Bahce%'"
  );
  console.log("Universities named Bahcesehir:");
  console.log(unis);

  await connection.end();
}
check();
