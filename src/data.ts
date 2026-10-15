export type Meal={id:string;name:string;category:'Kahvaltı'|'Öğle'|'Akşam'|'Ara öğün';kcal:number;protein:number;carb:number;fat:number;fiber:number;ingredients:string[];recipe:string;allergens:string[];emoji:string};
export const meals:Meal[]=[
{id:'egg',name:'Yumurta, söğüş ve tam tahıllı ekmek',category:'Kahvaltı',kcal:350,protein:19,carb:30,fat:17,fiber:6,ingredients:['2 yumurta','1 dilim tam tahıllı ekmek','Domates','Salatalık','5 zeytin'],recipe:'Yumurtayı haşlayın, sebzelerle ve ekmekle servis edin.',allergens:['Yumurta','Gluten'],emoji:'🍳'},
{id:'oat',name:'Yoğurtlu yulaf ve meyve',category:'Kahvaltı',kcal:340,protein:16,carb:48,fat:9,fiber:8,ingredients:['5 yemek kaşığı yulaf','1 kase yoğurt','1 küçük elma','2 ceviz'],recipe:'Yulafı yoğurtla karıştırıp meyve ve ceviz ekleyin.',allergens:['Süt','Gluten','Sert kabuklu'],emoji:'🥣'},
{id:'cheese',name:'Peynirli sebzeli kahvaltı',category:'Kahvaltı',kcal:370,protein:20,carb:35,fat:17,fiber:7,ingredients:['60 g az tuzlu peynir','2 dilim tam tahıllı ekmek','Domates','Roka'],recipe:'Malzemeleri yıkayıp tabakta servis edin.',allergens:['Süt','Gluten'],emoji:'🧀'},
{id:'lentil',name:'Mercimek çorbası ve salata',category:'Öğle',kcal:440,protein:20,carb:65,fat:12,fiber:15,ingredients:['1,5 kepçe mercimek çorbası','Mevsim salatası','1 dilim tam tahıllı ekmek','1 kase yoğurt'],recipe:'Az yağlı mercimek çorbasını salata ve yoğurtla servis edin.',allergens:['Gluten','Süt'],emoji:'🥗'},
{id:'chicken',name:'Izgara tavuk ve bulgur',category:'Öğle',kcal:550,protein:43,carb:53,fat:17,fiber:8,ingredients:['130 g tavuk göğsü','5 yemek kaşığı bulgur','Mevsim salatası','1 tatlı kaşığı zeytinyağı'],recipe:'Tavuğu iyice pişirin, bulgur ve salatayla servis edin.',allergens:['Gluten'],emoji:'🍗'},
{id:'beans',name:'Kuru fasulye ve cacık',category:'Öğle',kcal:520,protein:25,carb:70,fat:15,fiber:18,ingredients:['1 porsiyon kuru fasulye','4 yemek kaşığı bulgur','1 kase cacık'],recipe:'Fasulyeyi az yağla pişirip bulgur ve cacıkla sunun.',allergens:['Süt','Gluten'],emoji:'🫘'},
{id:'fish',name:'Fırında balık ve sebze',category:'Akşam',kcal:530,protein:39,carb:42,fat:22,fiber:9,ingredients:['150 g balık','Fırın sebzeleri','1 küçük haşlanmış patates','1 tatlı kaşığı zeytinyağı'],recipe:'Balığı güvenli iç sıcaklığa kadar pişirin; sebzelerle servis edin.',allergens:['Balık'],emoji:'🐟'},
{id:'veg',name:'Zeytinyağlı sebze ve yoğurt',category:'Akşam',kcal:470,protein:20,carb:55,fat:19,fiber:12,ingredients:['1 porsiyon sebze yemeği','1 kase yoğurt','2 dilim tam tahıllı ekmek'],recipe:'Mevsim sebzelerini az yağla pişirin.',allergens:['Süt','Gluten'],emoji:'🥦'},
{id:'meat',name:'Etli sebze yemeği',category:'Akşam',kcal:560,protein:38,carb:45,fat:24,fiber:10,ingredients:['120 g yağsız et','Mevsim sebzeleri','4 yemek kaşığı bulgur','Salata'],recipe:'Eti ve sebzeleri iyi pişirin, bulgurla servis edin.',allergens:['Gluten'],emoji:'🍲'},
{id:'fruit',name:'Meyve ve çiğ badem',category:'Ara öğün',kcal:185,protein:5,carb:23,fat:9,fiber:5,ingredients:['1 orta boy meyve','10 çiğ badem'],recipe:'Meyveyi yıkayıp bademle tüketin.',allergens:['Sert kabuklu'],emoji:'🍎'},
{id:'yogurt',name:'Yoğurt ve tarçın',category:'Ara öğün',kcal:155,protein:10,carb:15,fat:6,fiber:1,ingredients:['1 kase sade yoğurt','Tarçın','Yarım küçük muz'],recipe:'Malzemeleri karıştırın.',allergens:['Süt'],emoji:'🥛'},
{id:'hummus',name:'Humus ve havuç',category:'Ara öğün',kcal:200,protein:7,carb:24,fat:9,fiber:7,ingredients:['3 yemek kaşığı humus','1 havuç'],recipe:'Havuç dilimlerini humusla servis edin.',allergens:['Susam'],emoji:'🥕'}
];
export const conditions=[
{name:'Fibromiyalji',emoji:'🌿',text:'Fibromiyaljiyi iyileştirdiği kanıtlanmış tek bir diyet yoktur. Dengeli beslenme, uyku düzeni ve tolere edilebilen kademeli hareket önemlidir.'},
{name:'Demir eksikliği',emoji:'🩸',text:'Et, baklagil ve yeşil yapraklı sebzeler demir kaynaklarıdır. C vitamini bitkisel demirin emilimini destekleyebilir. Eksikliğin nedeni hekimce araştırılmalıdır.'},
{name:'D vitamini eksikliği',emoji:'☀️',text:'Beslenme tek başına eksikliği gidermeye yetmeyebilir. Takviye dozu laboratuvar sonuçları ve hekim değerlendirmesine göre belirlenir.'},
{name:'Karaciğer yağlanması',emoji:'🫒',text:'Akdeniz tipi beslenme, düzenli hareket ve uygun kilo yönetimi destekleyicidir. Görüntülemedeki yağlanma derecesi fibrozis evresi değildir.'},
{name:'Lenfödem',emoji:'💚',text:'Hareket ve öz bakım bireysel değerlendirme gerektirir. Kompresyon veya sıvı kısıtlaması uygulama tarafından reçete edilmez.'},
{name:'Hipertansiyon',emoji:'❤️',text:'Tuz tüketiminin sınırlandırılması ve dengeli beslenme önemlidir. İlaçlar ve bireysel kısıtlamalar hekim tarafından yönetilir.'},
{name:'Tip 2 diyabet',emoji:'🩺',text:'Öğün düzeni, karbonhidrat kalitesi ve porsiyon kontrolü önemlidir. İlaç ve hipoglisemi riskleri kişiye özgü değerlendirilmelidir.'},
{name:'Yüksek kolesterol',emoji:'🥑',text:'Doymuş yağın sınırlandırılması ve liften zengin beslenme genel olarak yararlıdır.'},
{name:'Çölyak',emoji:'🌾',text:'Tanı doğrulandıktan sonra sıkı glutensiz beslenme gerektirir; çapraz bulaşma da önemlidir.'},
{name:'Böbrek hastalıkları',emoji:'💧',text:'Protein, potasyum, sodyum ve sıvı gereksinimi hastalığın evresine göre değişir. Otomatik klinik diyet oluşturulmaz.'}
];
export const exercises=[
{name:'Rahat tempolu yürüyüş',emoji:'🚶',duration:'10–20 dk',level:'Başlangıç',steps:'Düz zeminde rahat bir tempoyla yürüyün. Gerektiğinde kısa molalar verin.',warning:'Göğüs ağrısı, baş dönmesi veya belirgin nefes darlığında durun.'},
{name:'Sandalye üzerinde omuz hareketi',emoji:'🪑',duration:'3–5 dk',level:'Hafif',steps:'Dik oturun, omuzlarınızı yavaşça geriye ve öne yuvarlayın. Ağrısız aralıkta kalın.',warning:'Omuz yaralanması veya ağrıda zorlamayın.'},
{name:'Nazik boyun esnetme',emoji:'🧘',duration:'2–3 dk',level:'Hafif',steps:'Başınızı zorlamadan hafifçe sağa ve sola eğin; ani hareketlerden kaçının.',warning:'Uyuşma, baş dönmesi veya keskin ağrı olursa durun.'},
{name:'Otururken ayak bileği hareketi',emoji:'🦶',duration:'2–4 dk',level:'Hafif',steps:'Otururken ayak bileklerini yavaşça yukarı aşağı hareket ettirin.',warning:'Ani tek taraflı şişlik, kızarıklık veya ağrı varsa değerlendirme alın.'},
{name:'Denge çalışması',emoji:'⚖️',duration:'3–5 dk',level:'Orta',steps:'Sabit bir tezgâhın yanında, destek alarak kısa süre tek ayak üzerinde durmayı deneyin.',warning:'Düşme riski olanlar uzman desteği almalıdır.'}
];
export const sources=[{name:'Türkiye Beslenme Rehberi (TÜBER 2022)',url:'https://hsgm.saglik.gov.tr/',publisher:'T.C. Sağlık Bakanlığı'},{name:'Türkiye Fiziksel Aktivite Rehberi',url:'https://hsgm.saglik.gov.tr/',publisher:'T.C. Sağlık Bakanlığı'},{name:'TÜRKOMP – Ulusal Gıda Kompozisyon Veri Tabanı',url:'https://www.turkomp.gov.tr/',publisher:'TÜBİTAK / ilgili kurumlar'},{name:'WHO Healthy Diet',url:'https://www.who.int/news-room/fact-sheets/detail/healthy-diet',publisher:'Dünya Sağlık Örgütü'}];
export const days=['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];
export function dailyMenu(index:number,excluded:string[]=[]){return (['Kahvaltı','Öğle','Akşam','Ara öğün'] as const).map((category,offset)=>{const choices=meals.filter(m=>m.category===category&&!m.allergens.some(a=>excluded.includes(a)));return choices.length?choices[(index+offset)%choices.length]:null}).filter((x):x is Meal=>Boolean(x))}
