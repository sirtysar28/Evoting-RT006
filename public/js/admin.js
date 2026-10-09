/* ============================================================
   E-VOTING RT006 — Admin misc: dropzone, modal, captcha refresh
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  /* ---------- Dropzone upload Excel ---------- */
  const dz = document.getElementById('dropzone');
  const fileInput = document.getElementById('fileInput');
  const fileName = document.getElementById('fileName');
  if (dz && fileInput) {
    dz.addEventListener('click', () => fileInput.click());
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('dragover'));
    dz.addEventListener('drop', e => {
      e.preventDefault();
      dz.classList.remove('dragover');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileName();
      }
    });
    fileInput.addEventListener('change', showFileName);

    function showFileName() {
      if (fileInput.files.length) {
        fileName.innerHTML = `📄 File terpilih: <b>${fileInput.files[0].name}</b> (${(fileInput.files[0].size / 1024).toFixed(1)} KB)`;
        fileName.classList.add('alert', 'alert-info');
      }
    }
  }

  /* ---------- Refresh captcha ---------- */
  window.refreshCaptcha = function () {
    const img = document.getElementById('captchaImg');
    if (img) img.src = '/captcha?' + Date.now();
  };

  /* ---------- Modal open via data-attr ---------- */
  document.querySelectorAll('[data-open-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const m = document.getElementById(btn.dataset.openModal);
      m?.classList.add('open');
      document.body.style.overflow = 'hidden';
    });
  });
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-overlay')?.classList.remove('open');
      document.body.style.overflow = '';
    });
  });
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => {
      if (e.target === m) { m.classList.remove('open'); document.body.style.overflow = ''; }
    });
  });

  /* ---------- Toggle form edit calon ---------- */
  document.querySelectorAll('[data-toggle-edit]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.toggleEdit);
      target.style.display = target.style.display === 'none' ? 'block' : 'none';
    });
  });
});
