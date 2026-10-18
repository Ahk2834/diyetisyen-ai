export type DailyWisdom={text:string;theme:string;attribution:string;sourceUrl:string};
export const dailyWisdom:DailyWisdom[]=[
{text:"Gelin tanışık edelim işi kolay tutalım / Sevelim sevilelim dünya kimseye kalmaz",theme:"Sevgi",attribution:"Yunus Emre · Dîvân · Diyanet İşleri Başkanlığı yayını",sourceUrl:"https://dijital.diyanet.gov.tr/File/Download?id=4232&path=4232_1.pdf"},
{text:"Kanâat geld’ oturdu tahtı aldı / Harâmîler heman yollarda kaldı",theme:"Kanaat",attribution:"Yunus Emre · Risâletü’n-Nushiyye · Destân-ı Kanâat",sourceUrl:"https://www.ateslekarsilikveren.com/2018/07/risaletun-nushiyye-iii-destan-kanaat.html"},
{text:"Kanaati yâr edin uyma nefs dileğine",theme:"Ölçülülük",attribution:"Yunus Emre · Risâletü’n-Nushiyye ve Divan · Ketebe Yayınları",sourceUrl:"https://www.ketebe.com/risaletun-nushiyye-ve-divan-yunus-emre"}
];
/** Local calendar date selects the same sourced quotation throughout the day. */
export function wisdomForDate(date:Date):DailyWisdom{const start=new Date(date.getFullYear(),0,1);const day=Math.floor((new Date(date.getFullYear(),date.getMonth(),date.getDate()).getTime()-start.getTime())/86400000);return dailyWisdom[((day%dailyWisdom.length)+dailyWisdom.length)%dailyWisdom.length];}
