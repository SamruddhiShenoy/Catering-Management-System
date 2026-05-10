<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = '/Catering_Management_System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CaterBook — Catering Management System</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= $base_url ?>/css/styles.css?v=<?= time() ?>">
<!-- Animation Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://unpkg.com/scrollreveal"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
<style>
/* Adjustments for public pages to look like a real website */
body { overflow-y: auto; }
.public-navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 50px;
    background: var(--nav-bg);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid var(--border);
}
.public-nav-links a {
    color: var(--text2);
    text-decoration: none;
    margin-left: 30px;
    font-weight: 500;
    transition: 0.3s;
}
.public-nav-links a:hover { color: var(--gold); }
.hero-section {
    padding: 100px 50px;
    text-align: center;
    background: url('<?= $base_url ?>/images/hero_bg.png') center/cover no-repeat;
    min-height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}
.hero-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: var(--overlay);
    z-index: 1;
}
.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 64px;
    color: var(--gold);
    margin-bottom: 20px;
}
.hero-subtitle {
    font-size: 20px;
    color: var(--text2);
    max-width: 600px;
    margin-bottom: 40px;
    line-height: 1.6;
}
.section {
    padding: 80px 50px;
}
.section-title {
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    color: var(--text);
    margin-bottom: 50px;
}
.public-footer {
    text-align: center;
    padding: 40px;
    border-top: 1px solid var(--border);
    color: var(--text3);
}
.card-3d {
    transform-style: preserve-3d;
    transform: perspective(1000px);
}
.reveal { visibility: hidden; }
@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(1deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}
.floating-img {
    transition: all 0.5s ease;
}
.floating-img:hover {
    transform: scale(1.05) !important;
    z-index: 10 !important;
}

/* Spinning Plate Animation from Original */
.login-art {
  position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:flex-end;
}
.login-art::before {
  content:''; position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Ccircle cx='200' cy='200' r='180' fill='none' stroke='rgba(212,168,67,0.08)' stroke-width='1'/%3E%3Ccircle cx='200' cy='200' r='130' fill='none' stroke='rgba(212,168,67,0.06)' stroke-width='1'/%3E%3Ccircle cx='200' cy='200' r='80' fill='none' stroke='rgba(212,168,67,0.1)' stroke-width='1'/%3E%3Cpath d='M200 20 L380 200 L200 380 L20 200 Z' fill='none' stroke='rgba(212,168,67,0.05)' stroke-width='1'/%3E%3C/svg%3E") center/contain no-repeat;
  animation:spinArt 60s linear infinite;
}
@keyframes spinArt { to { transform:rotate(360deg); } }
.art-plate {
  position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
  width:220px; height:220px; border-radius:50%;
  background:radial-gradient(circle at 35% 35%,#3d2810,#1a0d04);
  box-shadow:0 20px 60px rgba(0,0,0,0.8), inset 0 2px 4px rgba(212,168,67,0.2), 0 0 0 2px rgba(212,168,67,0.15);
  display:flex; align-items:center; justify-content:center; font-size:80px;
  animation:platePulse 4s ease-in-out infinite;
}
@keyframes platePulse { 0%,100%{box-shadow:0 20px 60px rgba(0,0,0,0.8),0 0 0 2px rgba(212,168,67,0.15),0 0 40px rgba(212,168,67,0.1);} 50%{box-shadow:0 20px 60px rgba(0,0,0,0.8),0 0 0 2px rgba(212,168,67,0.3),0 0 80px rgba(212,168,67,0.2);} }

/* Chatbot Styles */
.chat-widget { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end; }
.chat-window { width: 350px; height: 450px; background: var(--surface); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: 0 20px 50px rgba(0,0,0,0.5); display: none; flex-direction: column; overflow: hidden; margin-bottom: 16px; transform-origin: bottom right; animation: scaleIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.chat-window.active { display: flex; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }
.chat-header { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); padding: 16px; color: #1a0d04; display: flex; justify-content: space-between; align-items: center; }
.chat-header h3 { font-size: 16px; font-family: 'DM Sans', sans-serif; display: flex; align-items: center; gap: 8px; }
.chat-close { background: none; border: none; color: #1a0d04; cursor: pointer; font-size: 18px; }
.chat-body { flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: var(--dark); }
.chat-msg { max-width: 80%; padding: 10px 14px; border-radius: 14px; font-size: 14px; line-height: 1.4; }
.chat-msg.bot { background: var(--dark3); border-bottom-left-radius: 4px; color: var(--text); align-self: flex-start; }
.chat-msg.user { background: var(--accent2); border-bottom-right-radius: 4px; color: #fff; align-self: flex-end; }
.chat-input-area { padding: 14px; background: var(--surface); border-top: 1px solid var(--border2); display: flex; gap: 8px; }
.chat-input { flex: 1; background: var(--dark3); border: 1px solid var(--border2); border-radius: 20px; padding: 10px 16px; color: var(--text); outline: none; font-size: 14px; }
.chat-input:focus { border-color: var(--gold); }
.chat-send { background: var(--gold); color: #1a0d04; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.chat-send:hover { transform: scale(1.1); }
.typing-dot { width: 6px; height: 6px; background: var(--text3); border-radius: 50%; display: inline-block; animation: typing 1.4s infinite ease-in-out both; }
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
@keyframes typing { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }

/* Theme Toggle Button specific to header/navbar */
.theme-toggle-btn { background: none; border: none; color: var(--text2); font-size: 18px; cursor: pointer; transition: 0.2s; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
.theme-toggle-btn:hover { background: var(--dark3); color: var(--gold); }
</style>
<script>
// Inline script to prevent flash of wrong theme on load
if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.add('light-mode');
}
</script>
</head>
<body class="light-mode-aware">
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>
<script>
function toast(msg, type = 'success') {
  const icons = { success:'fa-check-circle', error:'fa-times-circle', info:'fa-info-circle', warning:'fa-exclamation-triangle' };
  const colors = { success:'var(--green)', error:'var(--accent)', info:'var(--accent2)', warning:'var(--orange)' };
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<i class="fas ${icons[type]||icons.info}" style="color:${colors[type]};flex-shrink:0;font-size:16px"></i><span>${msg}</span>`;
  const container = document.getElementById('toastContainer');
  if (container) {
      container.appendChild(t);
      setTimeout(() => { t.style.animation='slideOut .3s ease forwards'; setTimeout(()=>t.remove(),300); }, 3500);
  } else {
      alert(msg);
  }
}
</script>
