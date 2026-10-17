import {readFileSync} from 'node:fs';
import {test} from 'node:test';
import assert from 'node:assert/strict';

test('account registration remains non-operational and does not simulate consent',()=>{
  const panel=readFileSync('src/AccountPanel.tsx','utf8');
  assert.match(panel,/Hesap oluşturma henüz etkin değil/);
  assert.match(panel,/herhangi bir açık rıza toplamaz/);
  assert.doesNotMatch(panel,/privacyConsent|setPrivacyConsent/);
  assert.doesNotMatch(panel,/action:\s*['"]register['"]/);
});
test('login request sends no health profile',()=>{
  const panel=readFileSync('src/AccountPanel.tsx','utf8');
  assert.match(panel,/JSON\.stringify\(\{action:'login',email,password\}\)/);
  assert.match(panel,/credentials:'same-origin'/);
});
