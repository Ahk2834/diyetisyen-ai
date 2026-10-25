import React, {useEffect, useState} from 'react';
import {GOOGLE_LOGIN_ENABLED, GOOGLE_WEB_CLIENT_ID} from './googleAuthConfig';
import './account-panel.css';

type GoogleCredentialResponse = {credential?: string};
type GoogleIdentity = {initialize: (options: {client_id: string; callback: (response: GoogleCredentialResponse) => void}) => void; renderButton: (element: HTMLElement, options: {theme: string; size: string; text: string}) => void};
declare global {interface Window {google?: {accounts?: {id?: GoogleIdentity}}}}

export function AccountPanel() {
  const googleButtonRef=React.useRef<HTMLDivElement>(null);
  const [email,setEmail]=useState('');
  const [googleCredential,setGoogleCredential]=useState('');
  const [invitationCode,setInvitationCode]=useState('');
  const [googleStatus,setGoogleStatus]=useState('');
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
          setGoogleCredential(credential);
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
            setGoogleStatus(String(result.status || ''));
            const messages:Record<string,string>={
              pending:'Google hesabınız doğrulandı. Hesabınızı etkinleştirmek için davet kodunuzu girin.',
              active:'Google ile giriş başarılı.',
              disabled:'Bu hesap devre dışı bırakılmış.',
              account_link_required:'Bu e-posta ile mevcut bir hesap var. Hesap eşleştirmesi için yöneticiye başvurun.',
              invalid_invitation:'Davet kodu geçersiz, süresi dolmuş veya daha önce kullanılmış.',
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

  async function activateInvitation(event:React.FormEvent) {
    event.preventDefault();
    if (!googleCredential) {setStatus('Önce Google ile giriş yapın.');return;}
    setLoading(true);
    try {
      const response=await fetch('/diyetisyen-ai/api/google-auth.php',{
        method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',
        body:JSON.stringify({credential:googleCredential,invitation_code:invitationCode.trim()})
      });
      const result=await response.json();
      if (response.ok && result.status==='active') {
        setGoogleStatus('active');setGoogleCredential('');setInvitationCode('');
        setStatus('Hesabınız etkinleştirildi. Google ile giriş başarılı.');
      } else if (result.status==='invalid_invitation') {
        setStatus('Davet kodu geçersiz, süresi dolmuş veya kullanılmış.');
      } else {
        setStatus('Etkinleştirme tamamlanamadı. Lütfen yeniden deneyin.');
      }
    } catch {setStatus('Sunucuya ulaşılamadı.');}
    finally {setLoading(false);}
  }
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
  return <section className="account-panel" aria-labelledby="account-title">
    <div className="account-panel__intro">
      <span className="account-panel__eyebrow">DİYETİSYEN AI · HESABIM</span>
      <h2 id="account-title">Hesabınıza hoş geldiniz</h2>
      <p>Sağlıklı yaşam yolculuğunuza güvenle devam edin.</p>
    </div>
    <div className="account-panel__content">
      {GOOGLE_LOGIN_ENABLED && <div className="account-panel__google">
        <div ref={googleButtonRef} className="account-panel__google-button" aria-label="Google ile giriş" />
      </div>}
      {googleStatus==='pending' && <form className="account-panel__form" onSubmit={activateInvitation}>
        <label htmlFor="dietai-invitation">Tek kullanımlık davet kodu</label>
        <input id="dietai-invitation" type="text" inputMode="text" autoComplete="off"
          placeholder="32 karakterlik davet kodu" maxLength={32} required
          pattern="[A-Fa-f0-9]{32}" value={invitationCode}
          onChange={e=>setInvitationCode(e.target.value)} />
        <button type="submit" disabled={loading || !googleCredential}>{loading?'Kontrol ediliyor…':'Hesabı etkinleştir'}</button>
      </form>}
      <div className="account-panel__divider"><span>E-posta ile giriş</span></div>
      <p className="account-panel__hint">E-posta ve parola ile giriş henüz etkin değil.</p>
      <form className="account-panel__form" onSubmit={signIn}>
        <label htmlFor="dietai-login-email">E-posta adresi</label>
        <input id="dietai-login-email" type="email" autoComplete="username" placeholder="ornek@eposta.com" required value={email} onChange={e=>setEmail(e.target.value)}/>
        <label htmlFor="dietai-login-password">Parola</label>
        <input id="dietai-login-password" type="password" autoComplete="current-password" placeholder="Parolanız" required value={password} onChange={e=>setPassword(e.target.value)}/>
        <button type="submit" disabled={loading}>{loading?'Kontrol ediliyor…':'Giriş yap'}</button>
      </form>
      {status&&<p className="account-panel__status" role="status">{status}</p>}
      <div className="account-panel__privacy">
        <span aria-hidden="true">♧</span>
        <p>Sağlık profiliniz giriş sırasında Google ile paylaşılmaz. Bu cihazdaki yerel kayıtlar henüz şifrelenmeden saklanır.</p>
      </div>
      <button className="account-panel__register-toggle" type="button" aria-expanded={showRegistration} onClick={()=>{setShowRegistration(x=>!x);setStatus('');}}>
        {showRegistration?'Kayıt bilgisini gizle':'Yeni hesap oluştur'}
      </button>
      {showRegistration&&<form className="account-panel__registration" onSubmit={registrationInfo}>
        <h3>Yeni hesap</h3>
        <p>Yeni hesap başvuruları Google üzerinden alınır ve yönetici onayı gerektirir. E-posta/parola ile kayıt henüz etkin değildir.</p>
        <p>Bu alan herhangi bir açık rıza toplamaz.</p>
        <button type="submit">Kayıt durumunu kontrol et</button>
      </form>}
    </div>
  </section>;
}
