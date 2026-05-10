/* ══════════════════════════════════════════
   PARTICLES
══════════════════════════════════════════ */
function initParticles() {
  const canvas = document.getElementById('particles-canvas');
  const ctx = canvas.getContext('2d');
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  window.addEventListener('resize', () => { canvas.width=window.innerWidth; canvas.height=window.innerHeight; });
  const particles = Array.from({length:60},()=>({
    x:Math.random()*canvas.width, y:Math.random()*canvas.height,
    vx:(Math.random()-.5)*.4, vy:(Math.random()-.5)*.4,
    r:Math.random()*2+1, a:Math.random()
  }));
  function frame() {
    ctx.clearRect(0,0,canvas.width,canvas.height);
    particles.forEach(p=>{
      p.x+=p.vx; p.y+=p.vy;
      if(p.x<0||p.x>canvas.width) p.vx*=-1;
      if(p.y<0||p.y>canvas.height) p.vy*=-1;
      ctx.beginPath();
      ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle=\`rgba(212,168,67,\${p.a*0.3})\`;
      ctx.fill();
    });
    particles.forEach((p,i)=>{
      for(let j=i+1;j<particles.length;j++){
        const d=Math.hypot(p.x-particles[j].x,p.y-particles[j].y);
        if(d<100){
          ctx.beginPath();
          ctx.moveTo(p.x,p.y);
          ctx.lineTo(particles[j].x,particles[j].y);
          ctx.strokeStyle=\`rgba(212,168,67,\${(1-d/100)*0.05})\`;
          ctx.lineWidth=0.5;
          ctx.stroke();
        }
      }
    });
    requestAnimationFrame(frame);
  }
  frame();
}
