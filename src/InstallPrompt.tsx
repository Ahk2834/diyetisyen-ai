import {useEffect,useState} from 'react';
type InstallEvent=Event & {prompt:()=>Promise<void>;userChoice:Promise<{outcome:string}>};
export function InstallPrompt(){
  const [install,setInstall]=useState<InstallEvent|null>(null);
  const [showHelp,setShowHelp]=useState(false);
  const [installed,setInstalled]=useState(false);
  const [isIOS,setIsIOS]=useState(false);
  useEffect(()=>{
    const onPrompt=(event:Event)=>{event.preventDefault();setInstall(event as InstallEvent)};
    const onInstalled=()=>{setInstalled(true);setInstall(null);setShowHelp(false)};
    window.addEventListener('beforeinstallprompt',onPrompt);
    window.addEventListener('appinstalled',onInstalled);
    if('serviceWorker' in navigator) void navigator.serviceWorker.register('/diyetisyen-ai/sw.js').catch(()=>{});
    setInstalled(window.matchMedia('(display-mode: standalone)').matches || (navigator as Navigator & {standalone?:boolean}).standalone===true);
    setIsIOS(/iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform==='MacIntel' && navigator.maxTouchPoints>1));
    return()=>{window.removeEventListener('beforeinstallprompt',onPrompt);window.removeEventListener('appinstalled',onInstalled)};
  },[]);
  if(installed)return null;
  return <div style={{position:'fixed',bottom:16,right:16,zIndex:1000,maxWidth:320,background:'#fff',border:'1px solid #b6d9c9',padding:12,borderRadius:12,boxShadow:'0 4px 24px #0002'}}>
    <button type="button" onClick={async()=>{if(install){await install.prompt();await install.userChoice;setInstall(null)}else setShowHelp(!showHelp)}} style={{background:'#147e65',color:'white',border:0,padding:'10px 14px',borderRadius:8,cursor:'pointer'}}>📱 Telefonuma Yükle</button>
    {showHelp&&<p style={{fontSize:13,margin:'8px 0 0'}}>{isIOS?'iPhone: Safari ile açın, Paylaş simgesine (kare içinden yukarı ok) dokunun, ardından “Ana Ekrana Ekle” seçeneğini ve “Ekle”yi seçin. iOS, sitenin tek dokunuşla simge oluşturmasına izin vermez.':'Android: Chrome menüsü (⋮) → Ana ekrana ekle seçeneğini kullanın. Tarayıcı otomatik yüklemeye izin vermiyorsa bu adım gereklidir.'}</p>}
  </div>
}
