import React, {useEffect, useState} from 'react';
import {GOOGLE_LOGIN_ENABLED, GOOGLE_WEB_CLIENT_ID} from './googleAuthConfig';

type GoogleCredentialResponse = {credential?: string};
type GoogleIdentity = {initialize: (options: {client_id: string; callback: (response: GoogleCredentialResponse) => void}) => void; renderButton: (element: HTMLElement, options: {theme: string; size: string; text: string}) => void};
declare global {interface Window {google?: {accounts?: {id?: GoogleIdentity}}}}

export function AccountPanel() {
  const googleButtonRef=React.useRef<HTMLDivElement>(null);
  const [email,setEmail]=useState('');
  const [password,setPassword]=useState('');
  const [status,setStatus]=useState('');
  const [loading,setLoading]=useState(false);
  const [showRegistration,setShowRegistration]=useState(false);

  useEffect(() => {
    if (!GOOGLE_LOGIN_ENABLED) return;
    let cancelled=false;
    const script=document.createElement('script');
    script.src='https://accounts.google.com/gsi/client';
    script.async=true;
    script.defer=true;
    script.onload=()=>{
      if(cancelled || !window.google?.accounts?.id || !googleButtonRef.current) return;
      window.google.accounts.id.initialize({
        client_id: GOOGLE_WEB_CLIENT_ID,
        callback: async ({credential})=>{
          if(!credential) {setStatus('Google kimlik bilgisi alınamadı.');return;}
          setLoading(true);
          try {
            const response=await fetch('/diyetisyen-ai/api/google-auth.php',{
              method:'POST',
              headers:{'Content-Type':'application/json'},
              credentials:'same-origin',
              body:JSON.stringify({credential})
            });
            const result=await response.json();
            if(!response.ok) {setStatus('Google giriş isteği tamamlanamadı.');return;}
            const messages:Record<string,string>={
              pending:'Başvurunuz alındı. Yönetici onayı bekleniyor.',
              active:'Google ile giriş başarılı.',
              disabled:'Bu hesap devre dışı bırakılmış.',
              account_link_required:'Bu e-posta ile mevcut bir hesap var. Hesap eşleştirmesi için yöneticiye başvurun.',
              session_not_enabled:'Hesabınız onaylı ancak güvenli oturum açma henüz etkin değil.'
            };
            setStatus(messages[result.status] || 'İşlem tamamlanamadı.');
          } catch {setStatus('Sunucuya ulaşılamadı.');}
          finally {setLoading(false);}
        }
      });
      window.google.accounts.id.renderButton(googleButtonRef.current,{theme:'outline',size:'large',text:'signin_with'});
    };
    script.onerror=()=>{if(!cancelled)setStatus('Google giriş bileşeni yüklenemedi.');};
    document.head.appendChild(script);
    return ()=>{cancelled=true;script.remove();};
  }, []);

  async function signIn(event:React.FormEvent) {
    event.preventDefault();
    setLoading(true);
    setStatus('');
    try {
      const response=await fetch('/diyetisyen-ai/api/auth.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body:JSON.stringify({action:'login',email,password})
      });
      const result=await response.json();
      if(response.ok&&result.ok) {
        setStatus('Giriş başarılı.');
        setPassword('');
      } else {
        setStatus(response.status===503?'Giriş hizmeti henüz etkin değil.':'Giriş yapılamadı. Bilgilerinizi kontrol edin.');
      }
    } catch {
      setStatus('Sunucuya ulaşılamadı.');
    } finally {
      setLoading(false);
    }
  }
  function registrationInfo(event:React.FormEvent) {
    event.preventDefault();
    setStatus('Hesap oluşturma henüz etkin değil. Herhangi bir kayıt verisi gönderilmedi.');
  }
  return <section className="account-panel">
    <h2>Hesap girişi</h2>
    <p>Hesap hizmeti etkinleştirildiğinde e-posta adresinizle giriş yapabilirsiniz.</p>
    {GOOGLE_LOGIN_ENABLED && <div ref={googleButtonRef} aria-label="Google ile giriş" />}
    <form onSubmit={signIn}>
      <label>E-posta<input type="email" autoComplete="username" required value={email} onChange={e=>setEmail(e.target.value)}/></label>
      <label>Parola<input type="password" autoComplete="current-password" required value={password} onChange={e=>setPassword(e.target.value)}/></label>
      <button disabled={loading}>{loading?'Kontrol ediliyor…':'Giriş yap'}</button>
    </form>
    {status&&<p role="status">{status}</p>}
    <p>Sağlık profiliniz giriş isteğiyle paylaşılmaz. Yerel kayıtlar bu cihazda şifrelenmeden saklanır.</p>
    <button type="button" onClick={()=>{setShowRegistration(x=>!x);setStatus('');}}>
      {showRegistration?'Kayıt bilgilendirmesini kapat':'Yeni hesap oluştur'}
    </button>
    {showRegistration&&<form onSubmit={registrationInfo}>
      <h3>Yeni hesap</h3>
      <p>Kayıt işlemi sunucuda henüz etkin değildir. Bu bölüm şu anda bilgilendirme amaçlıdır.</p>
      <p>Gizlilik ve veri işleme koşulları kesinleşmeden hesap kaydı açılmayacaktır. Bu ekran herhangi bir açık rıza toplamaz.</p>
      <button type="submit">Kayıt durumunu kontrol et</button>
    </form>}
  </section>;
}
