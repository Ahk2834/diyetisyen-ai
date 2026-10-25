import React, {useEffect, useRef, useState} from 'react';
import {GOOGLE_LOGIN_ENABLED, GOOGLE_WEB_CLIENT_ID} from './googleAuthConfig';
import './account-panel.css';
import {AdminInvitations} from './AdminInvitations';

type GoogleCredentialResponse = {credential?: string};
type GoogleIdentity = {
  initialize: (options: {client_id: string; callback: (response: GoogleCredentialResponse) => void}) => void;
  renderButton: (element: HTMLElement, options: {theme: string; size: string; text: string}) => void;
};
declare global {interface Window {google?: {accounts?: {id?: GoogleIdentity}}}}
type SessionUser = {id:number;email:string;first_name?:string|null;last_name?:string|null};
type SessionResult = {ok:boolean;authenticated?:boolean;user?:SessionUser|null;is_admin?:boolean};
type AuthStatus = 'pending'|'active'|'disabled'|'account_link_required'|'invalid_invitation'|'';
const API_BASE='/diyetisyen-ai/api/';

export function AccountPanel({onAuthChange}:{onAuthChange?:(authenticated:boolean)=>void}) {
  const googleButtonRef=useRef<HTMLDivElement>(null);
  const [session,setSession]=useState<SessionUser|null>(null);
  const [isAdmin,setIsAdmin]=useState(false);
  const [googleCredential,setGoogleCredential]=useState('');
  const [invitationCode,setInvitationCode]=useState('');
  const [googleStatus,setGoogleStatus]=useState<AuthStatus>('');
  const [status,setStatus]=useState('');
  const [loading,setLoading]=useState(false);

  async function refreshSession():Promise<boolean> {
    const response=await fetch(API_BASE+'google-session.php',{credentials:'same-origin',cache:'no-store'});
    if (!response.ok) throw new Error('session_unavailable');
    const result=await response.json() as SessionResult;
    if (!result.ok) throw new Error('session_unavailable');
    const user=result.authenticated && result.user ? result.user : null;
    setSession(user);
    setIsAdmin(Boolean(result.is_admin));
    onAuthChange?.(user!==null);
    return user!==null;
  }

  useEffect(()=>{
    void refreshSession().catch(()=>setStatus('Oturum bilgisi şu anda kontrol edilemiyor.'));
  },[]);

  useEffect(()=>{
    if (!GOOGLE_LOGIN_ENABLED || session) return;
    let cancelled=false;
    const script=document.createElement('script');
    script.src='https://accounts.google.com/gsi/client';
    script.async=true;
    script.defer=true;
    script.onload=()=>{
      if (cancelled || !window.google?.accounts?.id || !googleButtonRef.current) return;
      window.google.accounts.id.initialize({
        client_id:GOOGLE_WEB_CLIENT_ID,
        callback:async ({credential})=>{
          if (!credential) {setStatus('Google kimlik bilgisi alınamadı.');return;}
          setGoogleCredential(credential);
          setStatus('');
          setLoading(true);
          try {
            const response=await fetch(API_BASE+'google-auth.php',{
              method:'POST',headers:{'Content-Type':'application/json'},
              credentials:'same-origin',body:JSON.stringify({credential})
            });
            const result=await response.json() as {status?:AuthStatus};
            if (!response.ok) {setStatus('Google girişi tamamlanamadı. Lütfen tekrar deneyin.');return;}
            const nextStatus=result.status||'';
            setGoogleStatus(nextStatus);
            if (nextStatus==='active') {
              await refreshSession();
              setGoogleCredential('');
              setStatus('Google ile giriş başarılı.');
            } else {
              const messages:Record<string,string>={
                pending:'Google hesabınız doğrulandı. Hesabınızı etkinleştirmek için davet kodunuzu girin.',
                disabled:'Bu hesap devre dışı bırakılmış.',
                account_link_required:'Bu e-posta ile mevcut bir hesap var. Hesap eşleştirmesi için yöneticiye başvurun.',
                invalid_invitation:'Davet kodu geçersiz veya daha önce kullanılmış.'
              };
              setStatus(messages[nextStatus]||'İşlem tamamlanamadı.');
            }
          } catch {setStatus('Sunucuya ulaşılamadı.');}
          finally {setLoading(false);}
        }
      });
      window.google.accounts.id.renderButton(googleButtonRef.current,{theme:'outline',size:'large',text:'signin_with'});
    };
    script.onerror=()=>{if(!cancelled)setStatus('Google giriş bileşeni yüklenemedi.');};
    document.head.appendChild(script);
    return ()=>{cancelled=true;script.remove();};
  },[session]);

  async function activateInvitation(event:React.FormEvent) {
    event.preventDefault();
    if (!googleCredential) {setStatus('Önce Google hesabınızı seçin.');return;}
    setLoading(true);
    setStatus('');
    try {
      const response=await fetch(API_BASE+'google-auth.php',{
        method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',
        body:JSON.stringify({credential:googleCredential,invitation_code:invitationCode.trim()})
      });
      const result=await response.json() as {status?:AuthStatus};
      if (response.ok && result.status==='active') {
        const authenticated=await refreshSession();
        if (!authenticated) throw new Error('session_missing');
        setGoogleStatus('active');
        setGoogleCredential('');
        setInvitationCode('');
        setStatus('Hesabınız etkinleştirildi.');
      } else if (result.status==='invalid_invitation') {
        setStatus('Davet kodu geçersiz, süresi dolmuş veya daha önce kullanılmış.');
      } else if (result.status==='pending') {
        setStatus('Etkinleştirme için geçerli bir davet kodu gerekiyor.');
      } else {
        setStatus('Etkinleştirme tamamlanamadı. Lütfen tekrar deneyin.');
      }
    } catch {setStatus('Sunucuya ulaşılamadı veya oturum açılamadı.');}
    finally {setLoading(false);}
  }

  async function signOut() {
    setLoading(true);
    try {
      const response=await fetch(API_BASE+'google-session.php',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json'},
        body:'{}'
      });
      if (!response.ok) throw new Error('logout_failed');
      setSession(null);
      setIsAdmin(false);
      onAuthChange?.(false);
      setGoogleStatus('');
      setGoogleCredential('');
      setInvitationCode('');
      setStatus('Oturumunuz kapatıldı.');
    } catch {setStatus('Oturum kapatılamadı. Lütfen tekrar deneyin.');}
    finally {setLoading(false);}
  }

  const fullName=[session?.first_name,session?.last_name].filter(Boolean).join(' ');
  return <section className="account-panel" aria-labelledby="account-title">
    <div className="account-panel__intro">
      <span className="account-panel__eyebrow">DIET AI · HESABIM</span>
      <h2 id="account-title">{session?'Hesabım':'Hesabınıza hoş geldiniz'}</h2>
      <p>{session?'Güvenli oturumunuz açık.':'Google hesabınızla güvenle giriş yapın.'}</p>
    </div>
    <div className="account-panel__content">
      {session ? <div className="account-panel__registration">
        <h3>{fullName||'Hesap bilgileri'}</h3>
        <p><strong>E-posta:</strong> {session.email}</p>
        <p><strong>Hesap durumu:</strong> Aktif</p>
        {isAdmin&&<AdminInvitations/>}
        <button type="button" onClick={()=>void signOut()} disabled={loading}>Oturumu kapat</button>
      </div> : <>
        {GOOGLE_LOGIN_ENABLED && <div className="account-panel__google">
          <div ref={googleButtonRef} className="account-panel__google-button" aria-label="Google ile giriş"/>
        </div>}
        {googleStatus==='pending' && <form className="account-panel__form" onSubmit={activateInvitation}>
          <label htmlFor="dietai-invitation">Tek kullanımlık davet kodunuz</label>
          <input id="dietai-invitation" type="text" autoComplete="off" inputMode="text"
            placeholder="32 karakterlik kod" maxLength={32} pattern="[A-Fa-f0-9]{32}" required
            value={invitationCode} onChange={e=>setInvitationCode(e.target.value)}/>
          <button type="submit" disabled={loading||!googleCredential}>{loading?'Kontrol ediliyor…':'Hesabı etkinleştir'}</button>
        </form>}
        <p className="account-panel__hint">Üyelik davet koduyla etkinleştirilir. E-posta/parola ile kayıt açık değildir.</p>
      </>}
      {status&&<p className="account-panel__status" role="status">{status}</p>}
      <div className="account-panel__privacy">
        <span aria-hidden="true">♧</span>
        <p>Sağlık profiliniz Google giriş isteğiyle paylaşılmaz. Bu cihazdaki yerel kayıtlar henüz şifrelenmeden saklanır.</p>
      </div>
    </div>
  </section>;
}
