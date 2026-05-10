<?php
require_once 'config.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Fetch featured packages
$stmt = $pdo->query("SELECT * FROM packages WHERE featured = 1 LIMIT 3");
$featured_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all menu items for the animated marquees
$stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category, name");
$all_menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Split into two arrays for dual-row marquee
$half = max(1, ceil(count($all_menu_items) / 2));
$row1_items = array_slice($all_menu_items, 0, $half);
$row2_items = array_slice($all_menu_items, $half);
?>

<!-- Import Cormorant Garamond for the new elegant typography -->
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">

<style>
/* --- 3D Scene & New Index Styles --- */
.hero-3d {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  padding: 80px 60px; position: relative; overflow: hidden;
}
.hero-video-bg {
  position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0;
  object-fit: cover; filter: brightness(0.4) saturate(1.2);
}
/* Always dark hero for cinematic video contrast - works perfectly in both light/dark page modes */
.hero-overlay-3d {
  position: absolute; inset: 0; 
  background: linear-gradient(to right, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.85) 100%);
  z-index: 1;
}
.hero-video-bg {
  position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0;
  object-fit: cover; filter: brightness(0.5) contrast(1.1);
}
/* Force white text in hero section for guaranteed visibility */
.h1-3d, .hero-sub-3d, .stat-val, .stat-lbl, .hero-badge { color: #ffffff !important; }
.h1-3d em { color: var(--gold) !important; font-style: italic; }
.hero-badge { background: rgba(255,255,255,0.1); border-color: rgba(201,168,76,0.5); }
.stat-val { color: var(--gold) !important; }
.stat-lbl { opacity: 0.8; }
.hero-stats { border-color: rgba(255,255,255,0.1) !important; }
.hero-inner-3d { max-width: 1300px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; position: relative; z-index: 2; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(201,168,76,0.1); border: 1.5px solid var(--gold);
  padding: 10px 24px; border-radius: 30px; margin-bottom: 35px;
  font-size: 14px; letter-spacing: 2px; color: var(--gold); text-transform: uppercase; font-weight: 500;
}
.hero-badge span { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); animation: pulse-dot 2s infinite; }
@keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.7); } }
.h1-3d { font-family: 'Cormorant Garamond', serif; font-size: 80px; line-height: 1; font-weight: 300; margin-bottom: 30px; letter-spacing: -2px; }
.h1-3d em { font-style: italic; color: var(--gold); }
.hero-sub-3d { font-size: 22px; line-height: 1.7; color: var(--text2); margin-bottom: 40px; max-width: 700px; font-weight: 300; }
.hero-stats { display: flex; gap: 60px; margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border2); }
.stat-val { font-family: 'Cormorant Garamond', serif; font-size: 60px; font-weight: 300; color: var(--gold); line-height: 1; }
.stat-lbl { font-size: 14px; color: var(--text3); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 8px; }

/* 3D Plate Scene */
.plate-scene { position: relative; height: 560px; display: flex; align-items: center; justify-content: center; }
.scene-3d { perspective: 1000px; width: 400px; height: 400px; position: relative; }
.plate-ring {
  position: absolute; inset: 0; border-radius: 50%;
  border: 2px solid rgba(201,168,76,0.4);
  animation: orbit 12s linear infinite; transform-style: preserve-3d;
}
.plate-ring:nth-child(1) { animation-duration: 8s; border-color: rgba(201,168,76,0.5); }
.plate-ring:nth-child(2) { animation-duration: 14s; animation-direction: reverse; border-color: rgba(74,127,193,0.4); }
.plate-ring:nth-child(3) { animation-duration: 20s; border-color: rgba(124,92,191,0.3); border-style: dashed; }
@keyframes orbit { from { transform: rotateX(70deg) rotateZ(0deg); } to { transform: rotateX(70deg) rotateZ(360deg); } }
.plate-orb { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; transform-style: preserve-3d; }
.plate-main {
  width: 220px; height: 220px; border-radius: 50%;
  background: radial-gradient(circle at 35% 35%, #2A2A3E, #0D0D1A);
  border: 2px solid rgba(201,168,76,0.6);
  display: flex; align-items: center; justify-content: center; font-size: 96px;
  box-shadow: 0 0 80px rgba(201,168,76,0.15), 0 0 160px rgba(201,168,76,0.05), inset 0 2px 20px rgba(201,168,76,0.1);
  animation: float3d 6s ease-in-out infinite; position: relative; z-index: 5;
}
@keyframes float3d { 0%, 100% { transform: translateY(0) rotateX(0); } 50% { transform: translateY(-18px) rotateX(5deg); } }
.orbit-dot { position: absolute; width: 10px; height: 10px; border-radius: 50%; background: var(--gold); box-shadow: 0 0 20px var(--gold); }
.orbit-dot:nth-child(1) { animation: dot-orbit1 8s linear infinite; }
.orbit-dot:nth-child(2) { animation: dot-orbit2 14s linear infinite; background: var(--blue); box-shadow: 0 0 20px var(--blue); }
.orbit-dot:nth-child(3) { animation: dot-orbit3 20s linear infinite; background: var(--accent); box-shadow: 0 0 20px var(--accent); }
@keyframes dot-orbit1 { from { transform: rotateX(70deg) rotateZ(0deg) translateX(190px) rotateX(-70deg); } to { transform: rotateX(70deg) rotateZ(360deg) translateX(190px) rotateX(-70deg); } }
@keyframes dot-orbit2 { from { transform: rotateX(70deg) rotateZ(90deg) translateX(190px) rotateX(-70deg); } to { transform: rotateX(70deg) rotateZ(450deg) translateX(190px) rotateX(-70deg); } }
@keyframes dot-orbit3 { from { transform: rotateX(70deg) rotateZ(180deg) translateX(190px) rotateX(-70deg); } to { transform: rotateX(70deg) rotateZ(540deg) translateX(190px) rotateX(-70deg); } }

/* Floating Cards */
.float-card {
  position: absolute; background: rgba(21,21,36,0.9); border: 1px solid var(--border);
  border-radius: 12px; padding: 14px 18px; backdrop-filter: blur(12px);
  animation: floatCard 4s ease-in-out infinite; font-size: 13px; color: var(--text2); white-space: nowrap; z-index: 10;
}
.float-card:nth-child(1) { top: 60px; left: -50px; animation-delay: 0s; }
.float-card:nth-child(2) { bottom: 80px; right: -40px; animation-delay: 1.5s; }
.float-card:nth-child(3) { top: 160px; right: -70px; animation-delay: 3s; }
.float-card:nth-child(4) { bottom: 160px; left: -60px; animation-delay: 2s; }
@keyframes floatCard { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.float-card .fc-label { font-size: 11px; color: var(--text3); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
.float-card .fc-val { font-family: 'Cormorant Garamond', serif; font-size: 22px; color: var(--gold); font-weight: 400; }

/* Sections */
.section-3d { padding: 80px 60px; }
.section-inner { max-width: 1200px; margin: 0 auto; }
.sec-tag { font-size: 11px; letter-spacing: 2px; color: var(--gold); text-transform: uppercase; margin-bottom: 14px; }
.sec-title-3d { font-family: 'Cormorant Garamond', serif; font-size: 44px; font-weight: 300; line-height: 1.15; margin-bottom: 16px; }
.sec-title-3d em { font-style: italic; color: var(--gold); }
.sec-sub-3d { font-size: 16px; color: var(--text2); line-height: 1.7; max-width: 560px; font-weight: 300; }

/* Marquee / Menu Cards */
.menu-card {
  position: relative; border-radius: 12px; overflow: hidden; height: 210px; width: 280px;
  background: var(--card); border: 1px solid var(--border2);
  cursor: pointer; transition: all .4s; flex-shrink: 0;
}
.menu-card:hover { border-color: var(--gold); transform: translateY(-12px) scale(1.02); box-shadow: 0 25px 50px rgba(0,0,0,0.4); }
.menu-card:hover .mc-shine { opacity: 1; transform: translateX(100%); transition: all 0.8s; }
.mc-bg-emoji { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 100px; opacity: 0.06; filter: blur(2px); transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); z-index: 0; }
.menu-card:hover .mc-bg-emoji { opacity: 0.15; transform: scale(1.2) rotate(5deg); }
.mc-shine { position: absolute; inset: 0; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); transform: translateX(-100%); z-index: 1; }
.mc-gradient { position: absolute; inset: 0; background: linear-gradient(to top, rgba(8,8,13,0.95) 0%, transparent 100%); z-index: 2; }
.mc-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 18px 20px; z-index: 3; }
.mc-name { font-size: 17px; font-weight: 600; margin-bottom: 4px; color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.8); }
.mc-price { font-family: 'Cormorant Garamond', serif; font-size: 20px; color: var(--gold); font-weight: 400; text-shadow: 0 1px 3px rgba(0,0,0,0.8); }
.mc-emoji { font-size: 22px; position: absolute; bottom: 18px; right: 20px; z-index: 3; }

/* Marquee Animations */
.marquee-wrapper { width: 100%; overflow: hidden; margin-bottom: 30px; }
.marquee-track { display: flex; gap: 30px; width: max-content; }
:root { --marquee-speed: 50s; }
.track-left { animation: scrollLeft var(--marquee-speed) linear infinite; }
.track-right { animation: scrollRight var(--marquee-speed) linear infinite; }
.marquee-wrapper:hover .marquee-track { animation-play-state: paused; }
@keyframes scrollLeft { 0% { transform: translateX(0); } 100% { transform: translateX(calc(-33.333% - 10px)); } }
@keyframes scrollRight { 0% { transform: translateX(calc(-33.333% - 10px)); } 100% { transform: translateX(0); } }

/* Packages Grid */
.pkg-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 60px; perspective: 1000px; }
.pkg-card { border-radius: 16px; padding: 40px 32px; background: var(--card); border: 1px solid var(--border2); transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1); position: relative; overflow: hidden; transform-style: preserve-3d; }
.pkg-card:hover { 
  border-color: var(--gold); 
  box-shadow: 0 40px 80px rgba(0,0,0,0.5); 
  animation: pkg-float-bob 4s ease-in-out infinite;
}
@keyframes pkg-float-bob {
  0%, 100% { transform: translateY(-20px) rotateX(4deg) rotateY(2deg); }
  50% { transform: translateY(-30px) rotateX(6deg) rotateY(4deg); }
}
.pkg-card.featured { border-color: var(--gold); border-width: 1.5px; }
.pkg-card.featured::before { 
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; 
  background: linear-gradient(90deg, transparent, var(--gold), transparent); 
  animation: shine-move 3s infinite;
}
@keyframes shine-move { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
.pkg-glow { position: absolute; top: -40px; right: -40px; width: 180px; height: 180px; border-radius: 50%; background: radial-gradient(circle, rgba(201,168,76,0.15) 0%, transparent 70%); pointer-events: none; transition: all 0.8s; }
.pkg-card:hover .pkg-glow { transform: translate(-20px, 20px) scale(2.5); opacity: 0.3; }
.pkg-name { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 300; margin-bottom: 8px; color: var(--text); transition: color 0.3s; }
.pkg-card:hover .pkg-name { color: var(--gold); }
.pkg-price { font-family: 'Cormorant Garamond', serif; font-size: 52px; font-weight: 300; color: var(--gold); line-height: 1; margin-bottom: 6px; transition: transform 0.4s; }
.pkg-card:hover .pkg-price { transform: translateZ(30px); }
.pkg-pp { font-size: 13px; color: var(--text3); letter-spacing: 0.5px; }
.pkg-divider { height: 1px; background: var(--border2); margin: 24px 0; }
.pkg-feature { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; font-size: 14px; color: var(--text2); transition: all 0.4s; transform: translateX(0); }
.pkg-card:hover .pkg-feature { transform: translateX(5px); }
.pkg-feature-icon { width: 18px; height: 18px; border-radius: 50%; background: rgba(59,140,106,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--green); font-size: 10px; transition: all 0.3s; }
.pkg-card:hover .pkg-feature-icon { background: var(--green); color: #fff; transform: scale(1.2); }

/* Services */
.svc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 50px; }
.svc-card { padding: 36px 30px; border-radius: 12px; background: var(--card); border: 1px solid var(--border2); transition: all .4s; position: relative; overflow: hidden; }
.svc-card:hover { border-color: var(--border); transform: translateY(-4px); }
.svc-icon { font-size: 40px; margin-bottom: 24px; display: block; }
.svc-name { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 400; margin-bottom: 10px; color: var(--text); }
.svc-desc { font-size: 14px; color: var(--text2); line-height: 1.7; font-weight: 300; }
.svc-stripe { position: absolute; top: 0; left: 0; width: 3px; height: 0; background: var(--gold); transition: height .4s; }
.svc-card:hover .svc-stripe { height: 100%; }

/* Testimonials */
.testi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 50px; }
.testi-card { padding: 32px 30px; border-radius: 12px; background: var(--card); border: 1px solid var(--border2); }
.testi-stars { color: var(--gold); font-size: 15px; letter-spacing: 2px; margin-bottom: 20px; }
.testi-text { color: var(--text2); line-height: 1.8; font-style: italic; font-family: 'Cormorant Garamond', serif; font-size: 18px; margin-bottom: 24px; font-weight: 300; }
.testi-author { display: flex; align-items: center; gap: 14px; }
.testi-avatar { width: 48px; height: 48px; border-radius: 50%; background: rgba(201,168,76,0.1); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-size: 18px; color: var(--gold); }
.testi-name { font-size: 16px; font-weight: 500; color: var(--text); }
.testi-event { font-size: 13px; color: var(--text3); }

/* CTA Banner */
.cta-banner {
  margin: 80px 60px; border-radius: 24px; padding: 80px 60px;
  background: linear-gradient(135deg, rgba(201,168,76,0.06) 0%, rgba(21,21,36,0.9) 100%);
  border: 1px solid rgba(201,168,76,0.2); text-align: center; position: relative; overflow: hidden;
}
.cta-banner::before {
  content: ''; position: absolute; top: -80px; left: 50%; transform: translateX(-50%);
  width: 400px; height: 200px; border-radius: 50%;
  background: radial-gradient(circle, rgba(201,168,76,0.1) 0%, transparent 70%);
}
.cta-title { font-family: 'Cormorant Garamond', serif; font-size: 52px; font-weight: 300; margin-bottom: 18px; color: var(--text); }
.cta-title em { font-style: italic; color: var(--gold); }
.cta-sub { font-size: 17px; color: var(--text2); margin-bottom: 40px; font-weight: 300; }

/* Ticker Bar */
.ticker-bar {
  background: rgba(201,168,76,0.05); border-top: 1px solid var(--border2); border-bottom: 1px solid var(--border2);
  padding: 14px 0; overflow: hidden; position: relative; z-index: 99; margin-top: 0;
}
.ticker-inner { display: flex; animation: ticker 30s linear infinite; white-space: nowrap; }
.ticker-item { padding: 0 40px; font-size: 12px; color: var(--text3); display: flex; align-items: center; gap: 10px; letter-spacing: 0.5px; }

/* NAVBAR OVERRIDE - LUXURY STYLE */
.public-navbar .brand { 
  font-family: 'Cormorant Garamond', serif !important;
  font-size: 42px !important; 
  letter-spacing: 8px !important; 
  font-weight: 300 !important; 
  text-transform: uppercase !important;
  background: linear-gradient(to right, #c9a84c, #ffebad, #c9a84c);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  position: relative;
  display: inline-block;
  padding-bottom: 4px;
}
.public-navbar .brand::after {
  content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
  width: 40px; height: 1px; background: var(--gold); opacity: 0.6;
}
.public-navbar .nav-link { font-size: 14px !important; font-weight: 500; letter-spacing: 1px; }
.ticker-item span { color: var(--gold); }
@keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }

@media(max-width:900px){
  .hero-3d { padding: 100px 24px 60px; }
  .hero-inner-3d { grid-template-columns: 1fr; gap: 40px; }
  .plate-scene { height: 320px; }
  .scene-3d { width: 280px; height: 280px; }
  .plate-main { width: 160px; height: 160px; font-size: 70px; }
  .h1-3d { font-size: 48px; }
  .section-3d { padding: 60px 24px; }
  .float-card { display: none; }
  .cta-banner { margin: 40px 24px; padding: 50px 24px; }
  .cta-title { font-size: 36px; }
}

#bg-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
</style>

<canvas id="bg-canvas"></canvas>

<!-- Ticker -->
<div class="ticker-bar">
  <div class="ticker-inner" id="ticker">
    <div class="ticker-item">🔴 LIVE <span>247 Events Managed This Month</span></div>
    <div class="ticker-item">⭐ <span>4.98</span> Average Rating</div>
    <div class="ticker-item">🍽️ <span>12,000+</span> Guests Served This Week</div>
    <div class="ticker-item">📅 Next Available: <span>June 14</span></div>
    <div class="ticker-item">🏆 Award-Winning Chef: <span>Chef Marcus Holloway</span></div>
    <div class="ticker-item">🌍 Serving <span>18 Cities</span> Across India</div>
    <div class="ticker-item">✅ <span>Rs.4.2 Cr</span> Revenue This Quarter</div>
    <div class="ticker-item">🔴 LIVE <span>247 Events Managed This Month</span></div>
    <div class="ticker-item">⭐ <span>4.98</span> Average Rating</div>
    <div class="ticker-item">🍽️ <span>12,000+</span> Guests Served This Week</div>
    <div class="ticker-item">📅 Next Available: <span>June 14</span></div>
    <div class="ticker-item">🏆 Award-Winning Chef: <span>Chef Marcus Holloway</span></div>
    <div class="ticker-item">🌍 Serving <span>18 Cities</span> Across India</div>
    <div class="ticker-item">✅ <span>Rs.4.2 Cr</span> Revenue This Quarter</div>
  </div>
</div>

<!-- Hero Section -->
<section class="hero-3d">
  <!-- Cinematic Background Video -->
  <video class="hero-video-bg" autoplay muted loop playsinline poster="https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
    <source src="https://assets.mixkit.co/videos/preview/mixkit-chef-cooking-with-fire-in-a-pan-34442-large.mp4" type="video/mp4">
  </video>
  <div class="hero-overlay-3d"></div>
  
  <div class="hero-inner-3d">
    <div class="reveal visible">
      <div class="hero-badge"><span></span> Mangaluru's #1 Catering Platform</div>
      <h1 class="h1-3d">Unforgettable<br><em>Culinary</em><br>Excellence</h1>
      <p class="hero-sub-3d">CaterBook delivers bespoke dining experiences for weddings, corporate galas, and private celebrations — where taste meets sophisticated artistry.</p>
      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <a href="login.php" class="btn btn-gold" style="padding: 14px 34px;">Book Your Event</a>
        <a href="#menu" class="btn btn-ghost" style="padding: 14px 34px;">Explore Menu</a>
      </div>
      <div class="hero-stats">
        <div>
          <div class="stat-val">850+</div>
          <div class="stat-lbl">Events Hosted</div>
        </div>
        <div>
          <div class="stat-val">98%</div>
          <div class="stat-lbl">Satisfaction</div>
        </div>
        <div>
          <div class="stat-val">24+</div>
          <div class="stat-lbl">Expert Chefs</div>
        </div>
      </div>
    </div>

    <!-- 3D Plate Scene -->
    <div class="plate-scene reveal visible">
      <div class="scene-3d">
        <div class="plate-ring"></div>
        <div class="plate-ring"></div>
        <div class="plate-ring"></div>
        <div class="plate-orb">
          <div class="orbit-dot"></div>
          <div class="orbit-dot"></div>
          <div class="orbit-dot"></div>
          <div class="plate-main">🍽️</div>
        </div>
      </div>
      <!-- Float Cards -->
      <div class="float-card" style="top:40px;left:-60px;">
        <div class="fc-label">Today's Bookings</div>
        <div class="fc-val">14</div>
        <div style="font-size:11px;color:var(--green);margin-top:2px;">↑ 3 from yesterday</div>
      </div>
      <div class="float-card" style="bottom:60px;right:-50px;">
        <div class="fc-label">Revenue</div>
        <div class="fc-val">Rs.2.4L</div>
        <div style="font-size:11px;color:var(--green);margin-top:2px;">↑ 12% this week</div>
      </div>
      <div class="float-card" style="top:170px;right:-80px;">
        <div class="fc-label">Next Event</div>
        <div style="font-size:13px;color:var(--text);margin-top:2px;">🔔 Wedding • 3pm</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px;">Grand Ballroom, 320 guests</div>
      </div>
      <div class="float-card" style="bottom:160px;left:-70px;">
        <div class="fc-label">Chef On Duty</div>
        <div style="font-size:13px;color:var(--text);margin-top:2px;">👨‍🍳 Marcus H.</div>
        <div style="font-size:11px;color:var(--gold);margin-top:2px;">★ 4.98 rating</div>
      </div>
    </div>
  </div>
</section>

<!-- Menu Marquee Section -->
<section class="section-3d" id="menu" style="background: var(--dark2);">
  <div class="section-inner" style="max-width: 100%;">
    <div style="text-align:center; margin-bottom: 50px;" class="reveal visible">
      <div class="sec-tag">Our Cuisine</div>
      <div class="sec-title-3d" style="text-align:center;">Catering <em>Master Menu</em></div>
      <p class="sec-sub-3d" style="margin:0 auto;text-align:center;">Browse our curated collection of international and regional culinary delights, crafted by award-winning chefs.</p>
    </div>

    <!-- Row 1 Marquee (Scrolls Left) -->
    <div class="marquee-wrapper">
        <div class="marquee-track track-left">
            <?php 
            if (!empty($row1_items)):
                $display_row1 = array_merge($row1_items, $row1_items, $row1_items);
                foreach($display_row1 as $item): 
            ?>
                <div class="menu-card">
                    <?php if($item['image_path']): ?>
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                    <?php else: ?>
                        <div class="mc-bg-emoji"><?= $item['emoji'] ?></div>
                    <?php endif; ?>
                    <div class="mc-shine"></div>
                    <div class="mc-gradient"></div>
                    <div class="mc-content">
                        <div class="mc-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="mc-price">Rs.<?= number_format($item['price'], 2) ?></div>
                    </div>
                    <div class="mc-emoji"><?= $item['emoji'] ?></div>
                </div>
            <?php 
                endforeach; 
            endif;
            ?>
        </div>
    </div>

    <!-- Row 2 Marquee (Scrolls Right) -->
    <div class="marquee-wrapper">
        <div class="marquee-track track-right">
            <?php 
            if (!empty($row2_items)):
                $display_row2 = array_merge($row2_items, $row2_items, $row2_items);
                foreach($display_row2 as $item): 
            ?>
                <div class="menu-card">
                    <?php if($item['image_path']): ?>
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                    <?php else: ?>
                        <div class="mc-bg-emoji"><?= $item['emoji'] ?></div>
                    <?php endif; ?>
                    <div class="mc-shine"></div>
                    <div class="mc-gradient"></div>
                    <div class="mc-content">
                        <div class="mc-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="mc-price">Rs.<?= number_format($item['price'], 2) ?></div>
                    </div>
                    <div class="mc-emoji"><?= $item['emoji'] ?></div>
                </div>
            <?php 
                endforeach; 
            endif;
            ?>
        </div>
    </div>
  </div>
</section>

<!-- Packages Section -->
<section class="section-3d" id="packages">
  <div class="section-inner">
    <div class="reveal visible" style="text-align: center; margin-bottom: 50px;">
      <div class="sec-tag">Event Packages</div>
      <div class="sec-title-3d">Bespoke <em>Event Packages</em></div>
      <p class="sec-sub-3d" style="margin: 0 auto;">Tailored experiences for every occasion — from intimate gatherings to grand celebrations.</p>
    </div>
    <div class="pkg-grid">
      <?php foreach($featured_packages as $pkg): ?>
          <?php 
              $includes = json_decode($pkg['includes'], true) ?: []; 
              $is_gold = $pkg['color'] === 'gold';
              $is_platinum = $pkg['color'] === 'platinum';
              $is_featured = $is_gold || $is_platinum;
              
              $tier_label = 'Classic Tier';
              $tier_color = 'var(--text3)';
              
              if ($pkg['color'] === 'silver') {
                  $tier_label = 'Silver Tier';
                  $tier_color = 'var(--text3)';
              } elseif ($pkg['color'] === 'gold') {
                  $tier_label = '★ Gold Tier';
                  $tier_color = 'var(--gold)';
              } elseif ($pkg['color'] === 'platinum') {
                  $tier_label = 'Platinum Tier';
                  $tier_color = 'var(--accent)';
              }
          ?>
          <div class="pkg-card <?= $is_featured ? 'featured' : '' ?> reveal visible">
            <?php if($is_featured): ?><div class="pkg-glow"></div><?php endif; ?>
            <div style="font-size:11px;letter-spacing:1.5px;color:<?= $tier_color ?>;text-transform:uppercase;margin-bottom:16px;">
                <?= $tier_label ?> <?php if($is_gold): ?><span style="background:rgba(201,168,76,0.1);padding:2px 10px;border-radius:10px;font-size:10px;margin-left:5px;">MOST POPULAR</span><?php endif; ?>
            </div>
            <div class="pkg-name"><?= htmlspecialchars($pkg['name']) ?></div>
            <div class="pkg-price">Rs.<?= number_format($pkg['price'], 0) ?></div>
            <div class="pkg-pp">per person</div>
            <div class="pkg-divider"></div>
            <?php foreach($includes as $inc): ?>
                <div class="pkg-feature"><div class="pkg-feature-icon">✓</div><?= htmlspecialchars($inc) ?></div>
            <?php endforeach; ?>
            <a href="login.php" class="btn <?= $is_featured ? 'btn-gold' : 'btn-ghost' ?>" style="display:block; text-align:center; margin-top:20px;">Book Now →</a>
          </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Services Section -->
<section class="section-3d" id="services" style="background: var(--dark3);">
  <div class="section-inner">
    <div class="reveal visible" style="text-align: center; margin-bottom: 40px;">
      <div class="sec-tag">What We Offer</div>
      <div class="sec-title-3d">Crafted for <em>Every Occasion</em></div>
    </div>
    <div class="svc-grid">
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">💍</div><div class="svc-name">Wedding Catering</div><div class="svc-desc">From intimate ceremonies to grand celebrations — multi-day menus, themed cuisines, and bridal table experiences crafted with love.</div></div>
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">🏢</div><div class="svc-name">Corporate Events</div><div class="svc-desc">Professional catering for conferences, product launches, and annual galas. Punctual, presentation-perfect, and always impressive.</div></div>
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">🎉</div><div class="svc-name">Private Celebrations</div><div class="svc-desc">Birthdays, anniversaries, and house parties — we bring restaurant-quality dining directly to your chosen venue.</div></div>
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">🍳</div><div class="svc-name">Live Cooking Stations</div><div class="svc-desc">Interactive chef stations — biryani dum, dosa counters, wood-fired pizza, and carving stations for theatrical dining experiences.</div></div>
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">🌍</div><div class="svc-name">International Cuisine</div><div class="svc-desc">Authentic global menus: Mediterranean, Pan-Asian, Continental, and local cuisine curated by specialist chefs.</div></div>
      <div class="svc-card reveal visible"><div class="svc-stripe"></div><div class="svc-icon">🚚</div><div class="svc-name">On-Site Logistics</div><div class="svc-desc">Full setup, service, and breakdown — including portable kitchens, serving equipment, and professional wait staff teams.</div></div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="section-3d" id="about">
  <div class="section-inner">
    <div style="text-align:center;" class="reveal visible">
      <div class="sec-tag">Client Stories</div>
      <div class="sec-title-3d" style="text-align:center;">Trusted by <em>Thousands</em></div>
    </div>
    <div class="testi-grid">
      <div class="testi-card reveal visible">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-text">"CaterBook turned our wedding into a true culinary journey. Every dish was flawless and our 400 guests couldn't stop praising the food."</div>
        <div class="testi-author"><div class="testi-avatar">PR</div><div><div class="testi-name">Priya Rao</div><div class="testi-event">Wedding · 400 Guests</div></div></div>
      </div>
      <div class="testi-card reveal visible">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-text">"Our annual conference looked world-class. The corporate plating and live stations were a massive hit with our international delegates."</div>
        <div class="testi-author"><div class="testi-avatar">AS</div><div><div class="testi-name">Arvind Shenoy</div><div class="testi-event">Corporate Gala · 250 Delegates</div></div></div>
      </div>
      <div class="testi-card reveal visible">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-text">"The Royal Affair package exceeded every expectation. The chef table experience was something out of a Michelin dream. Exceptional."</div>
        <div class="testi-author"><div class="testi-avatar">NK</div><div><div class="testi-name">Nandini Kamath</div><div class="testi-event">Private Celebration · 80 Guests</div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Banner -->
<div class="section-inner" style="padding-bottom: 80px;">
    <div class="cta-banner reveal visible">
      <div class="cta-title">Ready to Create Something <em>Extraordinary?</em></div>
      <p class="cta-sub" style="color: var(--text2);">Join 850+ events we've elevated. Let's begin crafting your perfect experience.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="login.php" class="btn btn-gold" style="font-size:15px;padding:16px 42px;">Book Your Event</a>
        <a href="tel:+918001234567" class="btn btn-ghost" style="font-size:15px;padding:16px 42px;">Call Us Now</a>
      </div>
    </div>
</div>

<script>
// ─── Canvas BG — Deep space particles ───
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('bg-canvas');
    if(canvas) {
        const ctx = canvas.getContext('2d');
        let W, H, particles = [], mouse = {x:0, y:0};

        function resize(){W=canvas.width=innerWidth;H=canvas.height=innerHeight;}
        resize();
        window.addEventListener('resize',resize);

        function mkParticle(){
          return {
            x:Math.random()*W, y:Math.random()*H,
            r:Math.random()*1.4+0.2,
            vx:(Math.random()-0.5)*0.3,
            vy:(Math.random()-0.5)*0.3,
            alpha:Math.random()*0.6+0.1,
            color:Math.random()<0.15?'rgba(201,168,76,':'rgba(255,255,255,'
          };
        }
        for(let i=0;i<160;i++) particles.push(mkParticle());

        window.addEventListener('mousemove',e=>{mouse.x=e.clientX;mouse.y=e.clientY;});

        function drawConnections(){
          particles.forEach((p,i)=>{
            particles.slice(i+1,i+6).forEach(q=>{
              const d=Math.hypot(p.x-q.x,p.y-q.y);
              if(d<120){
                ctx.beginPath();
                ctx.moveTo(p.x,p.y);ctx.lineTo(q.x,q.y);
                ctx.strokeStyle=`rgba(201,168,76,${0.04*(1-d/120)})`;
                ctx.lineWidth=0.5;ctx.stroke();
              }
            });
          });
        }

        function animate(){
          ctx.clearRect(0,0,W,H);
          drawConnections();
          particles.forEach(p=>{
            const dx=p.x-mouse.x, dy=p.y-mouse.y, dist=Math.hypot(dx,dy);
            if(dist<180){
              p.x+=dx/dist*0.5;p.y+=dy/dist*0.5;
            }
            p.x+=p.vx;p.y+=p.vy;
            if(p.x<0)p.x=W;if(p.x>W)p.x=0;
            if(p.y<0)p.y=H;if(p.y>H)p.y=0;
            ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
            ctx.fillStyle=p.color+p.alpha+')';ctx.fill();
          });
          requestAnimationFrame(animate);
        }
        animate();
    }
    
    // Counter Animation
    document.querySelectorAll('.stat-val').forEach(el=>{
      const txt=el.textContent;
      const num=parseFloat(txt.replace(/[^0-9.]/g,''));
      const suffix=txt.replace(/[0-9.]/g,'');
      let start=null;
      const io=new IntersectionObserver(entries=>{
        if(entries[0].isIntersecting){
          io.disconnect();
          const dur=1800;
          function step(ts){
            if(!start)start=ts;
            const p=Math.min((ts-start)/dur,1);
            const eased=1-Math.pow(1-p,3);
            el.textContent=(num*eased).toFixed(num%1?1:0)+suffix;
            if(p<1)requestAnimationFrame(step);
          }
          requestAnimationFrame(step);
        }
      },{threshold:0.5});
      io.observe(el);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
