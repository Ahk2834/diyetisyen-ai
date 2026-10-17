import {readFileSync,existsSync} from 'node:fs';
import {test} from 'node:test';
import assert from 'node:assert/strict';

test('verification page ships as Vite public asset',()=>{
  const path='public/verify-email/index.html';
  assert.ok(existsSync(path));
  const html=readFileSync(path,'utf8');
  assert.match(html,/action:\s*'verify_email'/);
  assert.match(html,/\/diyetisyen-ai\/api\/auth\.php/);
  assert.match(html,/history\.replaceState/);
  assert.match(html,/credentials:\s*'same-origin'/);
  assert.match(html,/role="status"/);
});
