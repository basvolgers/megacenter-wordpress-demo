// Inloggen op de lokale Playground-site (standaard testaccount van WordPress Playground: admin / password).
// Alleen bedoeld voor de lokale demo op 127.0.0.1.
export async function login(page, base) {
  await page.goto(base + '/wp-login.php', { waitUntil: 'domcontentloaded', timeout: 120000 });
  if (!(await page.locator('#user_login').count())) return; // al ingelogd
  await page.fill('#user_login', process.env.MC_USER || 'admin');
  await page.fill('#user_pass', process.env.MC_PASS || 'password');
  await Promise.all([page.waitForURL(/wp-admin/, { timeout: 120000 }), page.click('#wp-submit')]);
}
