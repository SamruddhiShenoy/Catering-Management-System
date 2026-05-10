/* ══════════════════════════════════════════
   MODAL HELPERS
══════════════════════════════════════════ */
function openModal(title, body, onConfirm, confirmLabel = 'Save', cancelLabel = 'Cancel') {
  const modal = document.getElementById('modalContent');
  modal.innerHTML = `
    <h2>${title}</h2>
    ${body}
    <div class="modal-footer">
      ${cancelLabel ? \`<button class="btn btn-ghost" onclick="closeModal()">\${cancelLabel}</button>\` : ''}
      ${onConfirm ? \`<button class="btn btn-gold" onclick="modalConfirm()">\${confirmLabel || 'Save'}</button>\` : ''}
    </div>
  `;
  document._modalConfirm = onConfirm;
  document.getElementById('modalOverlay').classList.add('open');
}

function modalConfirm() { if(document._modalConfirm) document._modalConfirm(); }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
document.getElementById('modalOverlay').addEventListener('click', e => { if(e.target===document.getElementById('modalOverlay')) closeModal(); });
