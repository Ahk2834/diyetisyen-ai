import type {Meal} from './data';

/** A demo menu must not be presented as a complete plan if a meal group is missing. */
export type MenuAudit = {
  complete: boolean;
  missingCategories: string[];
  warnings: string[];
  estimatedCalories: number;
};

export function auditIllustrativeMenu(
  menu: readonly Meal[],
  mealsPerDay: number,
  forbiddenAllergens: readonly string[],
  excludedIngredients: readonly string[] = [],
): MenuAudit {
  const expected = mealsPerDay === 3
    ? ['Kahvaltı', 'Öğle', 'Akşam']
    : ['Kahvaltı', 'Öğle', 'Akşam', 'Ara öğün'];
  const present = new Set<Meal['category'] | string>(menu.map(meal => meal.category));
  const missingCategories = expected.filter(category => !present.has(category));
  const normalizedExclusions = excludedIngredients.map(x => x.trim().toLocaleLowerCase('tr-TR')).filter(Boolean);
  const warnings: string[] = [];
  if (missingCategories.length) warnings.push('Örnek menüde eksik öğün grupları var: ' + missingCategories.join(', ') + '.');
  if (menu.some(meal => meal.allergens.some(a => forbiddenAllergens.includes(a)))) {
    warnings.push('Menüde kaçınılması gereken bir alerjen bildirimi bulunuyor.');
  }
  if (menu.some(meal => normalizedExclusions.some(term =>
    meal.name.toLocaleLowerCase('tr-TR').includes(term) ||
    meal.ingredients.some(i => i.toLocaleLowerCase('tr-TR').includes(term))
  ))) warnings.push('Menüde kaçınılan bir içerik bulunuyor.');
  if (menu.some(meal => !Number.isFinite(meal.kcal) || meal.kcal <= 0)) {
    warnings.push('Bir veya daha fazla öğünün enerji tahmini geçersiz.');
  }
  if (menu.length === 0) warnings.push('Uygun örnek öğün bulunamadı.');
  return {
    complete: warnings.length === 0,
    missingCategories,
    warnings,
    estimatedCalories: menu.reduce((sum, meal) => sum + (Number.isFinite(meal.kcal) ? meal.kcal : 0), 0),
  };
}
