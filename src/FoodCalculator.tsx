import {useState} from 'react';
type Food={name:string;category:string;kcal:number;protein?:number;carbs?:number;fat?:number;fiber?:number;source?:string};
const foods:Food[]=[
{name:'Mezgit (çiğ)',category:'Balık',kcal:70,protein:15.27,carbs:0,fat:.97,source:'https://turkomp.tarimorman.gov.tr/food-mezgit-90'},
{name:'Hamsi (çiğ)',category:'Balık',kcal:197,protein:16.95,carbs:0,fat:14.36,source:'https://turkomp.tarimorman.gov.tr/food-hamsi-81'},
{name:'Süt (UHT, yağsız)',category:'Süt ürünleri',kcal:34,protein:2.81,carbs:5.52,fat:.11,source:'https://turkomp.tarimorman.gov.tr/food-sut-inek-uht-yagsiz-100-ml-de-1-5-g-dan-az-sut-yagi-20'},
{name:'Çam balı',category:'Tatlılar',kcal:327,protein:.13,carbs:81.73,fat:0,source:'https://turkomp.tarimorman.gov.tr/food-bal-cam-287'},
{name:'Zeytinyağı (sızma)',category:'Yağlar',kcal:900,source:'https://turkomp.tarimorman.gov.tr/component_result-enerji-2'},
{name:'Elma',category:'Meyveler',kcal:52},{name:'Muz',category:'Meyveler',kcal:89},{name:'Armut',category:'Meyveler',kcal:57},{name:'Portakal',category:'Meyveler',kcal:47},{name:'Çilek',category:'Meyveler',kcal:32},
{name:'Domates',category:'Sebzeler',kcal:18},{name:'Salatalık',category:'Sebzeler',kcal:15},{name:'Havuç',category:'Sebzeler',kcal:41},{name:'Kabak',category:'Sebzeler',kcal:17},{name:'Brokoli',category:'Sebzeler',kcal:34},
{name:'Patates (haşlanmış)',category:'Tahıl ve baklagiller',kcal:87},{name:'Pirinç (pişmiş)',category:'Tahıl ve baklagiller',kcal:130},{name:'Bulgur (pişmiş)',category:'Tahıl ve baklagiller',kcal:83},{name:'Mercimek (haşlanmış)',category:'Tahıl ve baklagiller',kcal:116},{name:'Nohut (haşlanmış)',category:'Tahıl ve baklagiller',kcal:164},
{name:'Yumurta (haşlanmış)',category:'Diğer',kcal:155},{name:'Tavuk göğsü (pişmiş)',category:'Diğer',kcal:165},{name:'Yoğurt (sade)',category:'Süt ürünleri',kcal:61},
{name:'Mercimek çorbası (örnek)',category:'Ev yemekleri',kcal:75},{name:'Menemen (örnek)',category:'Ev yemekleri',kcal:110},{name:'Kuru fasulye yemeği (örnek)',category:'Ev yemekleri',kcal:140},{name:'Sebze yemeği (örnek)',category:'Ev yemekleri',kcal:90}
];
const format=(n:number)=>n.toLocaleString('tr-TR',{maximumFractionDigits:1});
export function FoodCalculator(){
 const [food,setFood]=useState(foods[0].name);
 const [grams,setGrams]=useState('100');
 const [query,setQuery]=useState('');
 const [category,setCategory]=useState('Tümü');
 const categories=['Tümü',...Array.from(new Set(foods.map(f=>f.category)))];
 const filtered=foods.filter(f=>(category==='Tümü'||f.category===category)&&f.name.toLocaleLowerCase('tr-TR').includes(query.trim().toLocaleLowerCase('tr-TR')));
 const selected=foods.find(f=>f.name===food);
 const amount=Number(grams);
 const valid=grams.trim()!==''&&Number.isFinite(amount)&&amount>0&&amount<=5000;
 const factor=amount/100;
 return <section className="foodcalc">
  <div className="foodcalc-heading"><div><span className="foodcalc-eyebrow">BESİN DEĞERİ HESAPLAMA</span><h2>Kalori ve besin değerleri</h2><p>Besininizi seçin, tükettiğiniz miktarı yazın; sonucu anında görün.</p></div><div className="foodcalc-icon" aria-hidden="true">🥗</div></div>
  <div className="foodcalc-layout">
   <div className="foodcalc-form">
    <label htmlFor="foodcalc-search">1. Besin ara</label><input id="foodcalc-search" type="search" value={query} onChange={e=>setQuery(e.target.value)} placeholder="Örn. muz, mercimek, balık"/>
    <label htmlFor="foodcalc-category">Besin grubu</label><select id="foodcalc-category" value={category} onChange={e=>setCategory(e.target.value)}>{categories.map(c=><option key={c}>{c}</option>)}</select>
    <label htmlFor="foodcalc-food">2. Besin seç</label><select id="foodcalc-food" value={filtered.some(f=>f.name===food)?food:(filtered[0]?.name||'')} disabled={filtered.length===0} onChange={e=>setFood(e.target.value)}>{filtered.map(f=><option key={f.name} value={f.name}>{f.name}</option>)}</select>
    {filtered.length===0&&<p className="foodcalc-error">Bu aramada besin bulunamadı. Arama sözcüğünü veya grubu değiştirin.</p>}
    <label htmlFor="foodcalc-grams">3. Tüketilen miktar (gram)</label><input id="foodcalc-grams" type="number" min="1" max="5000" step="1" value={grams} onChange={e=>setGrams(e.target.value)} aria-invalid={!valid}/>
    <div className="foodcalc-quick" aria-label="Hızlı miktar seçimi">{[50,100,150,200,250].map(g=><button type="button" className={amount===g?'active':''} key={g} onClick={()=>setGrams(String(g))}>{g} g</button>)}</div>
    <p className="foodcalc-hint">Yenilebilir kısmın ağırlığını girin. Çiğ ve pişmiş besinler farklı değerler taşıyabilir.</p>
   </div>
   <div className="foodcalc-result" aria-live="polite">
    <span className="foodcalc-result-label">HESAPLANAN ENERJİ</span>
    {valid&&filtered.length>0&&selected?(()=>{const f=filtered.find(x=>x.name===food)||filtered[0];return <><div className="foodcalc-calories">{format(f.kcal*factor)} <small>kcal</small></div><strong>{f.name}</strong><p>{format(amount)} gram · 100 gramda {format(f.kcal)} kcal</p>
    {f.source?<><div className="foodcalc-source verified">TÜRKOMP verisi · 100 g yenilebilir kısım</div><a href={f.source} target="_blank" rel="noopener noreferrer">Resmî veri kaynağını görüntüle ↗</a></>:<div className="foodcalc-source">Yaklaşık örnek değer · Resmî veriyle doğrulanmadı</div>}
    {f.protein!==undefined&&<div className="foodcalc-macros"><div><span>Protein</span><strong>{format(f.protein*factor)} g</strong></div><div><span>Karbonhidrat</span><strong>{format((f.carbs||0)*factor)} g</strong></div><div><span>Yağ</span><strong>{format((f.fat||0)*factor)} g</strong></div></div>}
    </>})():<p className="foodcalc-error">Hesaplama için listeden besin seçin ve 1–5000 gram arasında bir miktar girin.</p>}
   </div>
  </div>
  <div className="foodcalc-guidance"><strong>Kaynak ve kullanım bilgisi</strong><p>Sağlıklı beslenme yaklaşımı için Sağlık Bakanlığı Türkiye Beslenme Rehberi (TÜBER 2022) esas alınmıştır. Doğrulanmış besin değerleri ise Tarım ve Orman Bakanlığının TÜRKOMP veri tabanından alınmıştır. Diğer değerler yaklaşık örnektir; pişirme yağı, tarif, marka ve porsiyon farklılıkları sonucu değiştirir.</p><a href="https://hsgm.saglik.gov.tr/depo/birimler/saglikli-beslenme-ve-hareketli-hayat-db/Dokumanlar/Rehberler/Turkiye_Beslenme_Rehber_TUBER_2022_min.pdf" target="_blank" rel="noopener noreferrer">TÜBER 2022 rehberini aç ↗</a></div>
 </section>;
}
