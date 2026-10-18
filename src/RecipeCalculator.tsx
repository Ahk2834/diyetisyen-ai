import {useState} from 'react';
import {recipeTemplates} from './recipeTemplates';
import type {Food} from './FoodCalculator';
type Ingredient={id:number;food:string;grams:string};
export function RecipeCalculator({foods}:{foods:Food[]}){
 const [ingredients,setIngredients]=useState<Ingredient[]>([{id:1,food:'',grams:''}]);
 const [nextId,setNextId]=useState(2);
 const [recipeName,setRecipeName]=useState('');
 const [servings,setServings]=useState('4');
 const [cookedWeight,setCookedWeight]=useState('');
 const [preset,setPreset]=useState('');
 const loadPreset=()=>{const t=recipeTemplates.find(x=>x.name===preset);if(!t)return;const found=t.ingredients.filter(x=>foods.some(f=>f.name===x.food));if(!found.length)return;setIngredients(found.map((x,i)=>({id:i+1,food:x.food,grams:String(x.grams)})));setNextId(found.length+1);setRecipeName(t.name);setServings(String(t.servings));setCookedWeight('');};
 const sorted=[...foods].sort((a,b)=>a.name.localeCompare(b.name,'tr-TR'));
 const update=(id:number,patch:Partial<Ingredient>)=>setIngredients(rows=>rows.map(row=>row.id===id?{...row,...patch}:row));
 const rows=ingredients.map(row=>({...row,selected:foods.find(f=>f.name===row.food),amount:Number(row.grams)}));
 const hasInvalid=rows.some(r=>r.food!==''&&(!Number.isFinite(r.amount)||r.amount<=0||r.amount>10000));
 const total=rows.reduce((sum,r)=>sum+(r.selected&&r.grams.trim()!==''&&r.amount>0&&r.amount<=10000?r.selected.kcal*r.amount/100:0),0);
 const count=Number(servings),weight=Number(cookedWeight);
 const validServings=servings.trim()!==''&&Number.isInteger(count)&&count>=1&&count<=100;
 const validWeight=cookedWeight.trim()===''||(Number.isFinite(weight)&&weight>0&&weight<=100000);
 const canCalculate=rows.some(r=>r.selected&&r.grams.trim()!==''&&r.amount>0)&&!hasInvalid&&validServings&&validWeight;
 const fmt=(n:number)=>n.toLocaleString('tr-TR',{maximumFractionDigits:1});
 return <section className="recipecalc" aria-label="Tarif malzemelerine göre kalori hesaplayıcı">
  <div className="recipecalc-title"><div><span className="foodcalc-eyebrow">TÜRK MUTFAĞI · EV YEMEKLERİ</span><h3>Tarifime göre hesapla</h3><p>Malzemeleri ve gramlarını ekleyin. Tencerenin toplamını ve porsiyon başına yaklaşık kaloriyi görün.</p></div><span aria-hidden="true">🍲</span></div>
  <div className="recipecalc-templates"><strong>Hazır Türk yemekleri ({recipeTemplates.length} tarif)</strong><p>Tarifi seçin, örnek malzemeleri yükleyin ve miktarlarını düzenleyin.</p><select aria-label="Hazır tarif seçimi" value={preset} onChange={e=>setPreset(e.target.value)}><option value="">Tarif seçiniz...</option>{recipeTemplates.map(t=><option key={t.name} value={t.name}>{t.category} — {t.name}</option>)}</select><button type="button" disabled={!preset} onClick={loadPreset}>Tarifi yükle</button></div>
  <label className="recipecalc-label" htmlFor="recipe-name">Yemek adı (isteğe bağlı)</label><input id="recipe-name" value={recipeName} maxLength={80} onChange={e=>setRecipeName(e.target.value)} placeholder="Örn. Annemin kuru fasulyesi"/>
  <div className="recipecalc-rows">{ingredients.map((row,i)=><div className="recipecalc-row" key={row.id}>
   <label>Malzeme {i+1}<select value={row.food} onChange={e=>update(row.id,{food:e.target.value})}><option value="">Malzeme seçin...</option>{sorted.map(f=><option key={f.name} value={f.name}>{f.name}</option>)}</select></label>
   <label>Miktar (g)<input type="number" min="1" max="10000" value={row.grams} onChange={e=>update(row.id,{grams:e.target.value})} placeholder="Gram"/></label>
   <button className="recipecalc-remove" type="button" aria-label={`Malzeme ${i+1} satırını sil`} disabled={ingredients.length===1} onClick={()=>setIngredients(x=>x.filter(y=>y.id!==row.id))}>Sil</button>
  </div>)}</div>
  <button type="button" className="recipecalc-add" onClick={()=>{setIngredients(x=>[...x,{id:nextId,food:'',grams:''}]);setNextId(n=>n+1)}} disabled={ingredients.length>=25}>+ Malzeme ekle</button>
  <div className="recipecalc-meta"><label>Kaç eşit porsiyon?<span className="recipecalc-help">Yemeğin tamamını kaç kişilik eşit miktara ayıracaksınız? Örneğin 8 yazarsanız toplam yemek 8 eşit parçaya bölünür. Bu, otomatik olarak 8 kâse veya 8 tabak demek değildir.</span><input type="number" min="1" max="100" step="1" value={servings} onChange={e=>setServings(e.target.value)}/></label><label>Pişmiş yemeğin toplam ağırlığı (g) · isteğe bağlı<span className="recipecalc-help">Pişirme bittikten sonra yemeği tencere hariç tartın. Girerseniz 1 porsiyonun kaç gram olduğunu da hesaplarız.</span><input type="number" min="1" max="100000" value={cookedWeight} onChange={e=>setCookedWeight(e.target.value)} placeholder="Örn. 1200"/></label></div>
  {canCalculate?<div className="recipecalc-summary" role="status"><span>{recipeName.trim()||'Tarifiniz'} · yaklaşık enerji</span><div className="recipecalc-results"><div><small>Tüm tarif</small><strong>{fmt(total)} kcal</strong></div><div><small>1 eşit porsiyon · toplam {count} porsiyon</small><strong>{fmt(total/count)} kcal</strong></div>{cookedWeight.trim()!==''&&<div><small>1 porsiyonun ağırlığı</small><strong>{fmt(weight/count)} g</strong></div>}{cookedWeight.trim()!==''&&<div><small>100 g pişmiş yemek</small><strong>{fmt(total*100/weight)} kcal</strong></div>}</div><p className="recipecalc-help">Porsiyon; tarifin eşit bölünmüş bir parçasıdır. Bir tabak veya kâsenin kaç porsiyon olduğu servis miktarına bağlıdır. Pişmiş ağırlığı girerseniz gram karşılığını görebilirsiniz.</p></div>:<p className="foodcalc-hint" role="status">Hesaplama için en az bir malzeme ve geçerli gramaj girin; porsiyon sayısını kontrol edin.</p>}
  <p className="recipecalc-disclaimer">Sonuçlar yaklaşık hesaplamadır, tıbbi beslenme önerisi değildir. Her malzemenin miktarını ve özellikle eklenen yağı dahil edin. Çiğ ve pişmiş değerleri karıştırmayın; pişirme sırasında su kaybı 100 gram başına kaloriyi değiştirir. Malzemelerin çoğu resmî veriyle doğrulanmamış örnek değerlerdir. Tarif bilgileri cihazınızda kaydedilmez.</p>
 </section>;
}
