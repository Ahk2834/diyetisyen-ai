/**
 * Diyetisyen AI Full - conservative planning eligibility checks.
 * This module does not diagnose conditions or authorize medical diets.
 * No personal information is persisted or sent to an external service.
 */
export type PlanningProfile = {
  age?: number | null;
  conditions?: readonly string[];
  allergies?: readonly string[];
  medications?: boolean;
  pregnantOrBreastfeeding?: boolean;
  eatingDisorderConcern?: boolean;
  swallowingDifficulty?: boolean;
};

export type PlanningDecision = {
  mode: 'general_information_only' | 'illustrative_plan';
  reasons: string[];
  requiresProfessionalReview: boolean;
};

const HIGH_RISK_TERMS = [
  'diyabet', 'diabetes', 'böbrek', 'renal', 'diyaliz',
  'kalp yetmezliği', 'karaciğer yetmezliği', 'çölyak',
  'kanser', 'onkoloji', 'yeme bozukluğu',
];

const normalize = (value: string) => value.toLocaleLowerCase('tr-TR').trim();

/** Fail closed when a safety-relevant answer is unknown. */
export function assessPlanningSafety(profile: PlanningProfile): PlanningDecision {
  const reasons: string[] = [];
  if (profile.age == null || !Number.isFinite(profile.age) || profile.age < 18 || profile.age > 100) {
    reasons.push('Yetişkin yaş bilgisi doğrulanamadı.');
  }
  if (profile.medications !== false) reasons.push('İlaç kullanımı açısından uzman değerlendirmesi gerekli.');
  if (profile.pregnantOrBreastfeeding !== false) reasons.push('Gebelik veya emzirme durumu uzman değerlendirmesi gerektirir.');
  if (profile.eatingDisorderConcern !== false) reasons.push('Yeme davranışı güvenliği uzman değerlendirmesi gerektirir.');
  if (profile.swallowingDifficulty !== false) reasons.push('Yutma güçlüğü açısından uzman değerlendirmesi gerekli.');
  if (!Array.isArray(profile.conditions)) {
    reasons.push('Sağlık durumu bilgisi doğrulanamadı.');
  } else if (profile.conditions.some(c => HIGH_RISK_TERMS.some(term => normalize(c).includes(term)))) {
    reasons.push('Bildirilen sağlık durumu kişiye özel diyet için uzman değerlendirmesi gerektirir.');
  }
  if (!Array.isArray(profile.allergies)) {
    reasons.push('Alerji bilgisi doğrulanamadı.');
  } else if (profile.allergies.length > 0) {
    reasons.push('Gıda alerjisi için doğrulanmış içerik ve çapraz bulaşma kontrolü gerekli.');
  }
  return {
    mode: reasons.length ? 'general_information_only' : 'illustrative_plan',
    reasons,
    requiresProfessionalReview: reasons.length > 0,
  };
}
