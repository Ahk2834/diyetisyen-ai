import test from 'node:test';
import assert from 'node:assert/strict';
import {assessPlanningSafety} from '../src/safety.ts';
import {auditIllustrativeMenu} from '../src/menuAudit.ts';

const safe = {
  age: 35, conditions: [], allergies: [], medications: false,
  pregnantOrBreastfeeding: false, eatingDisorderConcern: false,
  swallowingDifficulty: false,
};
test('healthy adult demo profile can receive illustrative menu', () => {
  assert.equal(assessPlanningSafety(safe).mode, 'illustrative_plan');
});
test('medication, allergy and swallowing concern prevent personal plan', () => {
  for (const change of [
    {medications: true},
    {allergies: ['Süt']},
    {swallowingDifficulty: true},
    {eatingDisorderConcern: true},
    {conditions: ['Tip 2 diyabet']},
  ]) {
    const result = assessPlanningSafety({...safe, ...change});
    assert.equal(result.mode, 'general_information_only');
    assert.ok(result.reasons.length);
  }
});
test('unknown safety information fails closed', () => {
  assert.equal(assessPlanningSafety({age: 40}).mode, 'general_information_only');
});
const meal = (category, allergens = [], ingredients = ['Sebze']) => ({
  id: category, name: category, category, kcal: 350, protein: 15,
  carb: 30, fat: 10, fiber: 5, ingredients, recipe: 'Örnek',
  allergens, emoji: '🥗',
});
const fullMenu = [
  meal('Kahvaltı'), meal('Öğle'), meal('Akşam'), meal('Ara öğün'),
];
test('four-meal demo menu passes completeness audit', () => {
  assert.equal(auditIllustrativeMenu(fullMenu, 4, []).complete, true);
});
test('missing snack makes four-meal menu incomplete', () => {
  const result = auditIllustrativeMenu(fullMenu.slice(0, 3), 4, []);
  assert.equal(result.complete, false);
  assert.deepEqual(result.missingCategories, ['Ara öğün']);
});
test('three-meal plan does not require snack', () => {
  assert.equal(auditIllustrativeMenu(fullMenu.slice(0, 3), 3, []).complete, true);
});
test('allergen and avoided ingredient invalidate menu', () => {
  const menu = [meal('Kahvaltı', ['Süt'], ['Yoğurt']), ...fullMenu.slice(1)];
  assert.equal(auditIllustrativeMenu(menu, 4, ['Süt']).complete, false);
  assert.equal(auditIllustrativeMenu(menu, 4, [], ['yoğurt']).complete, false);
});
