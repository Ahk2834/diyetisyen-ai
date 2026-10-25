import {readFileSync} from 'node:fs';
import {test} from 'node:test';
import assert from 'node:assert/strict';

test('account registration requires Google and a one-use invitation',()=>{
  const panel=readFileSync('src/AccountPanel.tsx','utf8');
  assert.match(panel,/Tek kullanımlık davet kodunuz/);
  assert.match(panel,/invitation_code:invitationCode\.trim\(\)/);
  assert.doesNotMatch(panel,/action:\s*['"]register['"]/);
  assert.doesNotMatch(panel,/privacyConsent|setPrivacyConsent/);
});
test('Google login sends no health profile and uses same-origin credentials',()=>{
  const panel=readFileSync('src/AccountPanel.tsx','utf8');
  assert.match(panel,/JSON\.stringify\(\{credential\}\)/);
  assert.match(panel,/credentials:'same-origin'/);
  assert.doesNotMatch(panel,/healthProfile|medicalHistory|localStorage/);
});
test('account panel supports session and logout',()=>{
  const panel=readFileSync('src/AccountPanel.tsx','utf8');
  assert.match(panel,/google-session\.php/);
  assert.match(panel,/Oturumu kapat/);
});
