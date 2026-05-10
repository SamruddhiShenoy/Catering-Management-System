/* ══════════════════════════════════════════
   TOAST
══════════════════════════════════════════ */
function toast(msg, type = 'success') {
  const icons = { success:'fa-check-circle', error:'fa-times-circle', info:'fa-info-circle', warning:'fa-exclamation-triangle' };
  const colors = { success:'var(--green)', error:'var(--accent)', info:'var(--accent2)', warning:'var(--orange)' };
  const t = document.createElement('div');
  t.className = \`toast \${type}\`;
  t.innerHTML = \`<i class="fas \${icons[type]||icons.info}" style="color:\${colors[type]};flex-shrink:0;font-size:16px"></i><span>\${msg}</span>\`;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => { t.style.animation='slideOut .3s ease forwards'; setTimeout(()=>t.remove(),300); }, 3500);
}
