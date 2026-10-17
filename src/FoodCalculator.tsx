import React,{useState} from 'react';
const foods=[['Elma',52],['Muz',89],['Armut',57],['Portakal',47],['Çilek',32],['Domates',18],['Salatalık',15],['Havuç',41],['Kabak',17],['Brokoli',34],['Patates (haşlanmış)',87],['Pirinç (pişmiş)',130],['Bulgur (pişmiş)',83],['Mercimek (haşlanmış)',116],['Nohut (haşlanmış)',164],['Yumurta (haşlanmış)',155],['Tavuk göğsü (pişmiş)',165],['Yoğurt (sade)',61],['Mercimek çorbası (örnek)',75],['Menemen (örnek)',110],['Kuru fasulye yemeği (örnek)',140],['Sebze yemeği (örnek)',90]] as const;
export function FoodCalculator(){
 const [food,setFood]=useState<string>(foods[0][0]);
 const [grams,setGrams]=useState('100');
 const [query,setQuery]=useState('');
 const selected=foods.find(([name])=>name===food);
 const amount=Number(grams);
 const valid=Number.isFinite(amount)&&amount>0&&amount<=5000;
 return <section className="profile-card">
 <h2>Kalori hesaplayıcı</h2>
 <p>Meyve, sebze ve yaygın ev yemeklerinin yaklaşık enerji değerini gramaja göre hesaplayın. Tarif ve pişirme yöntemi sonucu değiştirir.</p>
 <label>Besin ara<input value={query} onChange={e=>setQuery(e.target.value)} placeholder="Örn. elma, mercimek"/></label>
 <label>Besin<select value={food} onChange={e=>setFood(e.target.value)}>
 {foods.filter(([name])=>name.toLocaleLowerCase('tr-TR').includes(query.toLocaleLowerCase('tr-TR'))).map(([name])=><option key={name}>{name}</option>)}
 </select></label>
 <label>Miktar (gram)<input type="number" min="1" max="5000" value={grams} onChange={e=>setGrams(e.target.value)}/></label>
 {valid&&selected?<p role="status"><strong>Yaklaşık {Math.round(selected[1]*amount/100)} kcal</strong> · {selected[1]} kcal / 100 g</p>:<p role="status">Geçerli bir besin ve 1–5000 g arası miktar seçin.</p>}
 <p>Bu değerler örnek referanslardır; laboratuvar veya TÜRKOMP doğrulaması değildir.</p>
 </section>;
}
