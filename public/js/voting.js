/* ============================================================
   E-VOTING RT006 — Voting Flow
   Verifikasi NIK → Pilih Calon → Konfirmasi → Bukti (QR)
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  const nikInput     = document.getElementById('nikInput');
  const namaInput    = document.getElementById('namaInput');
  const verifyBtn    = document.getElementById('verifyBtn');
  const step1        = document.getElementById('step1');
  const step2        = document.getElementById('step2');
  const stepperEls   = document.querySelectorAll('.step');
  const cards        = document.querySelectorAll('.candidate-card');
  const modalConfirm = document.getElementById('modalConfirm');
  const modalReceipt = document.getElementById('modalReceipt');

  let verifiedToken = null;
  let selectedCandidate = null;
  let debounce = null;

  /* ---------- Step indicator helper ---------- */
  function setStep(n) {
    stepperEls.forEach((el, i) => {
      el.classList.toggle('active', i + 1 === n);
      el.classList.toggle('done', i + 1 < n);
      if (i + 1 < n) el.querySelector('.n').textContent = '✓';
      else el.querySelector('.n').textContent = String(i + 1);
    });
  }
  setStep(1);

  /* ---------- NIK input: hanya angka + counter ---------- */
  const counter = document.querySelector('.digit-counter');
  if (nikInput) {
    nikInput.addEventListener('input', () => {
      nikInput.value = nikInput.value.replace(/\D/g, '').slice(0, 16);
      if (counter) counter.textContent = `${nikInput.value.length}/16`;
      namaInput.value = '';
      verifiedToken = null;
      lockCards();
    });
  }

  /* ---------- AJAX verify NIK ---------- */
  verifyBtn?.addEventListener('click', async () => {
    const nik = nikInput.value.trim();
    if (nik.length !== 16) {
      showToast('NIK Belum Lengkap', 'Masukkan 16 digit NIK KTP dengan benar.', 'error');
      nikInput.focus();
      return;
    }

    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="spinner"></span> Memverifikasi...';

    try {
      const res = await fetch('/verify-nik', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ nik }),
      });
      const data = await res.json();

      if (data.status === 'success') {
        namaInput.value = data.voter.name;
        verifiedToken = data.token;
        unlockCards();
        setStep(2);
        showToast(data.title, `Selamat datang, ${data.voter.name}! Silakan pilih calon ketua RT.`, 'success');
        document.getElementById('votingSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        namaInput.value = '';
        verifiedToken = null;
        lockCards();
        setStep(1);
        showToast(data.title, data.message, 'error', 6000);
      }
    } catch {
      showToast('Koneksi Gagal', 'Terjadi kesalahan jaringan, coba lagi.', 'error');
    } finally {
      verifyBtn.disabled = false;
      verifyBtn.innerHTML = '🔍 Verifikasi NIK & Lanjut Memilih';
    }
  });

  /* ---------- Lock/unlock cards ---------- */
  function lockCards() {
    cards.forEach(c => {
      c.classList.add('locked');
      c.classList.remove('selected');
      let ov = c.querySelector('.lock-overlay');
      if (!ov) {
        ov = document.createElement('div');
        ov.className = 'lock-overlay';
        ov.innerHTML = `<div class="lock-inner">
            <span class="lock-icon">🔒</span>
            <p>Verifikasi NIK dulu untuk membuka</p>
          </div>`;
        c.appendChild(ov);
      }
    });
    selectedCandidate = null;
  }
  function unlockCards() {
    cards.forEach(c => {
      c.classList.remove('locked');
      c.querySelector('.lock-overlay')?.remove();
    });
  }
  lockCards();

  /* ---------- Pilih calon → modal konfirmasi ---------- */
  cards.forEach(card => {
    card.addEventListener('click', () => {
      if (!verifiedToken) return;
      cards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      selectedCandidate = {
        id: card.dataset.id,
        number: card.dataset.number,
        name: card.dataset.name,
      };
      document.getElementById('confirmVoterName').textContent = namaInput.value;
      document.getElementById('confirmVoterNik').textContent = maskNik(nikInput.value.trim());
      document.getElementById('confirmCandidate').textContent =
        `${selectedCandidate.name} (No. Urut ${String(selectedCandidate.number).padStart(2, '0')})`;
      openModal(modalConfirm);
    });
  });

  /* ---------- Batal konfirmasi ---------- */
  document.getElementById('btnCancelVote')?.addEventListener('click', () => {
    cards.forEach(c => c.classList.remove('selected'));
    selectedCandidate = null;
    closeModal(modalConfirm);
  });

  /* ---------- Submit suara ---------- */
  document.getElementById('btnSubmitVote')?.addEventListener('click', async () => {
    if (!selectedCandidate) return;
    const btn = document.getElementById('btnSubmitVote');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Menyimpan suara...';

    try {
      const res = await fetch('/vote', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ candidate_id: selectedCandidate.id }),
      });
      const data = await res.json();

      if (data.status === 'success') {
        closeModal(modalConfirm);
        showReceipt(data.receipt);
        resetFlow();
      } else {
        showToast(data.title || 'Gagal', data.message || 'Suara tidak dapat disimpan.', 'error', 6000);
        if (data.status === 'session_expired') resetFlow();
        btn.disabled = false;
        btn.innerHTML = '✅ Ya, Saya Yakin — Simpan Suara';
      }
    } catch {
      showToast('Koneksi Gagal', 'Terjadi kesalahan jaringan.', 'error');
      btn.disabled = false;
      btn.innerHTML = '✅ Ya, Saya Yakin — Simpan Suara';
    }
  });

  /* ---------- Bukti memilih + QR ---------- */
  function showReceipt(r) {
    document.getElementById('rName').textContent = r.name;
    document.getElementById('rNik').textContent = r.nik_mask;
    document.getElementById('rTime').textContent = r.time;
    document.getElementById('rTicket').textContent = r.ticket;

    const qrHolder = document.getElementById('qrHolder');
    qrHolder.innerHTML = '';
    const qrText = `TANDA BUKTI SAH VOTING RT 006/RW 001 JATIWARNA|TIKET:${r.ticket}|NAMA:${r.name}|STATUS:TERVERIFIKASI SAH`;
    try {
      if (typeof QRCode === 'undefined') throw new Error('no-lib');
      new QRCode(qrHolder, { text: qrText, width: 148, height: 148, colorDark: '#0f172a', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    } catch {
      // Fallback offline: tampilkan kode tiket besar
      qrHolder.innerHTML = `<div style="width:148px;height:148px;display:grid;place-items:center;border:2px solid #0f172a;border-radius:10px;font-weight:800;font-size:.68rem;text-align:center;padding:6px;">QR OFFLINE<br>${r.ticket}</div>`;
    }

    openModal(modalReceipt);
    setStep(3);
  }

  function resetFlow() {
    nikInput.value = '';
    if (counter) counter.textContent = '0/16';
    namaInput.value = '';
    verifiedToken = null;
    lockCards();
    setStep(1);
    const submitBtn = document.getElementById('btnSubmitVote');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '✅ Ya, Saya Yakin — Simpan Suara';
    nikInput.focus();
  }

  function maskNik(nik) {
    return nik.substring(0, 6) + '******' + nik.substring(12);
  }

  /* ---------- Modal helpers ---------- */
  function openModal(m) { m?.classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(m) { m?.classList.remove('open'); document.body.style.overflow = ''; }
  document.querySelectorAll('[data-close-modal]').forEach(btn =>
    btn.addEventListener('click', () => closeModal(btn.closest('.modal-overlay'))));
  document.querySelectorAll('.modal-overlay').forEach(m =>
    m.addEventListener('click', e => { if (e.target === m) closeModal(m); }));

  /* ---------- Cetak & unduh bukti ---------- */
  document.getElementById('btnPrintReceipt')?.addEventListener('click', () => window.print());

  /* ---------- Spinner style (injected) ---------- */
  const style = document.createElement('style');
  style.textContent = `
    .spinner {
      display: inline-block; width: 16px; height: 16px;
      border: 2.5px solid rgba(255,255,255,0.35);
      border-top-color: #fff; border-radius: 50%;
      animation: spin 0.7s linear infinite;
    }`;
  document.head.appendChild(style);
});
