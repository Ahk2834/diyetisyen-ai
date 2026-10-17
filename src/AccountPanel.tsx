import React, {useState} from 'react';

export function AccountPanel() {
  const [email,setEmail]=useState('');
  const [password,setPassword]=useState('');
  const [status,setStatus]=useState('');
  const [loading,setLoading]=useState(false);
  const [showRegistration,setShowRegistration]=useState(false);

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
