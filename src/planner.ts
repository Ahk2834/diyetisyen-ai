import type {Meal} from './data';
export type EnergyInput={age:number;height:number;weight:number;sex:string;activity:string;goal:string};
/** Mifflin–St Jeor: only for adult demo use; not clinical assessment. */
export function estimateEnergy(p:EnergyInput){
 if(!Number.isFinite(p.age)||p.age<18||p.age>100||!Number.isFinite(p.height)||p.height<120||p.height>230||!Number.isFinite(p.weight)||p.weight<30||p.weight>300||!['Kadın','Erkek'].includes(p.sex))return null;
 const bmr=Math.round(10*p.weight+6.25*p.height-5*p.age+(p.sex==='Erkek'?5:-161));
 const activity:Record<string,number>={'Hareketsiz':1.2,'Hafif aktif':1.375,'Orta aktif':1.55,'Çok aktif':1.725};
 const maintenance=Math.round(bmr*(activity[p.activity]??1.375));
 // Avoid prescribing an aggressive energy deficit. This is only an illustrative reference.
 const target=p.goal==='Kilo verme hakkında bilgi'?Math.max(p.sex==='Erkek'?1500:1200,maintenance-250):maintenance;
 return {bmr,maintenance,target,method:'Mifflin–St Jeor (tahmini)'};
}
export function portionFactor(target:number,menuCalories:number){if(!Number.isFinite(target)||!Number.isFinite(menuCalories)||menuCalories<=0)return 1;return Math.min(1.3,Math.max(.8,target/menuCalories))}
export function scaleMeal(m:Meal,factor:number):Meal{return {...m,kcal:Math.round(m.kcal*factor),protein:Math.round(m.protein*factor),carb:Math.round(m.carb*factor),fat:Math.round(m.fat*factor),fiber:Math.round(m.fiber*factor),name:m.name+ (Math.abs(factor-1)>.05?' · yaklaşık '+Math.round(factor*100)+'% porsiyon':'')}}
