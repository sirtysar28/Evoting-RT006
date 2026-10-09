<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Voting Ketua RT 006 / RW 001 — Kelurahan Jatiwarna</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @include('partials.head-meta')

  <!-- Font (non-blocking) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"></noscript>

  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  <!-- QR Code (untuk bukti memilih, async non-blocking) -->
  <script async src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <style>
    /* Halaman khusus voting */
    .verify-layout {
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 1.6rem;
      align-items: start;
    }
    @media (max-width: 980px) { .verify-layout { grid-template-columns: 1fr; } }

    .voter-profile {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      padding: 0.9rem 1rem;
      background: rgba(52, 211, 153, 0.1);
      border: 1px solid rgba(52, 211, 153, 0.35);
      border-radius: 16px;
      margin-bottom: 1rem;
      animation: popIn 0.5s var(--ease);
    }
    .voter-profile .avatar {
      width: 46px; height: 46px;
      border-radius: 50%;
      background: var(--grad-emerald);
      display: grid; place-items: center;
      font-weight: 800; font-size: 1.1rem; color: #052e1c;
      flex-shrink: 0;
    }
    .voter-profile .vp-name { font-weight: 800; font-size: 0.92rem; }
    .voter-profile .vp-sub { font-size: 0.72rem; color: var(--emerald); font-weight: 600; }

    .security-note {
      font-size: 0.78rem;
      color: var(--muted);
      line-height: 1.8;
      background: rgba(148, 163, 184, 0.07);
      border: 1px dashed rgba(148, 163, 184, 0.3);
      border-radius: 14px;
      padding: 0.9rem 1rem;
      margin-top: 1.1rem;
    }
    .security-note b { color: var(--text); }

    .live-dot {
      display: inline-block; width: 8px; height: 8px;
      background: var(--emerald); border-radius: 50%;
      margin-right: 6px;
      animation: pulseGlow 2s infinite;
    }
  </style>
</head>
<body>

  <!-- ================= TOGGLE TEMA TERANG/GELAP ================= -->
  <button type="button" id="themeToggle" class="theme-toggle" title="Ganti mode terang / gelap" aria-label="Ganti tema">
    <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="5"/>
      <line x1="12" y1="1" x2="12" y2="3"/>
      <line x1="12" y1="21" x2="12" y2="23"/>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
      <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
      <line x1="1" y1="12" x2="3" y2="12"/>
      <line x1="21" y1="12" x2="23" y2="12"/>
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
      <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
  </button>

  <!-- ================= HERO PARALLAX ================= -->
  <section class="hero">
    <div class="parallax-layer" data-depth="26">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>
    <div class="parallax-layer" data-depth="46">
      <span class="float-icon" style="top:18%; left:12%;">🗳️</span>
      <span class="float-icon" style="top:28%; right:14%; animation-delay:-2s;">🛡️</span>
      <span class="float-icon" style="bottom:22%; left:20%; animation-delay:-4s;">📋</span>
      <span class="float-icon" style="bottom:30%; right:18%; animation-delay:-1s;">✅</span>
      <span class="float-icon" style="top:55%; left:6%; animation-delay:-5s;">🔐</span>
      <span class="float-icon" style="top:60%; right:7%; animation-delay:-3s;">⚡</span>
    </div>
    <div class="hero-grid"></div>

    <div class="hero-content" data-parallax>
      <img src="{{ asset('img/logo.png') }}" alt="Logo E-Voting RT 006" class="hero-logo">
      <span class="hero-badge"><span class="dot"></span> Pemilihan Ketua RT • Periode 2026–2031</span>
      <h1 class="hero-title">E-Voting Ketua RT 006<br>RW 001 Jatiwarna</h1>
      <p class="hero-subtitle">
        Sistem pemilihan ketua RT digital yang <b>aman, transparan, dan satu orang satu suara</b>.
        Kelurahan Jatiwarna, Kecamatan Pondok Melati — Kota Bekasi.
      </p>

      <div class="hero-cta">
        <a href="#verifikasi" class="btn btn-primary">🗳️ Mulai Memilih</a>
        <a href="{{ route('leaderboard') }}" class="btn btn-ghost">🏆 Leaderboard Hasil</a>
        <a href="{{ route('admin.login') }}" class="btn btn-ghost">🛡️ Login Panitia</a>
      </div>

      <div class="hero-stats">
        <div class="stat-chip"><div class="num" data-count="{{ $stats['dpt'] }}">0</div><div class="lbl">Warga DPT Terdaftar</div></div>
        <div class="stat-chip"><div class="num" data-count="{{ $stats['voted'] }}">0</div><div class="lbl">Sudah Memilih</div></div>
        <div class="stat-chip"><div class="num" data-count="{{ $candidates->count() }}">0</div><div class="lbl">Calon Ketua RT</div></div>
        <div class="stat-chip"><div class="num">100%</div><div class="lbl">Rahasia & Aman</div></div>
      </div>
    </div>

    <a href="#verifikasi" class="scroll-hint" aria-label="Scroll ke bawah">⌄</a>
  </section>

  <!-- ================= TICKER ================= -->
  <div class="ticker-band">
    <div class="ticker-track">
      @foreach([1, 2] as $rep)
        <span>RT 006 / RW 001 Jatiwarna</span>
        <span>Masa Bakti 2026 – 2031</span>
        <span>Satu Warga Satu Suara</span>
        <span>Verifikasi NIK Wajib</span>
        <span>Hasil Real-Time</span>
        <span>Dilarang Memilih Ganda</span>
      @endforeach
    </div>
  </div>

  <!-- ================= LANGKAH PEMILIHAN ================= -->
  <div class="section" style="padding-bottom:1rem;">
    <div class="stepper">
      <div class="step"><span class="n">1</span> Verifikasi NIK</div>
      <div class="step-line"></div>
      <div class="step"><span class="n">2</span> Pilih Calon</div>
      <div class="step-line"></div>
      <div class="step"><span class="n">3</span> Bukti Memilih</div>
    </div>
  </div>

  <!-- ================= VERIFIKASI + CALON ================= -->
  <div class="section" id="verifikasi" style="padding-top:1.5rem;">
    <div class="section-head reveal">
      <div class="eyebrow">Langkah 1 — Verifikasi Identitas</div>
      <h2>Masukkan NIK KTP Anda</h2>
      <p>Hanya NIK yang terdaftar pada <b>Daftar Pemilih Tetap (DPT)</b> hasil unggahan panitia yang dapat melanjutkan ke bilik suara digital.</p>
    </div>

    <div class="verify-layout">
      <!-- FORM VERIFIKASI -->
      <div class="glass-card reveal" data-delay="1" id="step1">
        <h3 style="margin:0 0 1.2rem; font-size:1.02rem; display:flex; align-items:center; gap:.6rem;">
          🔍 Verifikasi NIK Pemilih
        </h3>

        <div id="voterProfileBox" style="display:none;"></div>

        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label" for="nikInput">
            NIK KTP — 16 Digit
            <span class="digit-counter">0/16</span>
          </label>
          <input type="text" id="nikInput" class="form-control nik-input" inputmode="numeric"
                 placeholder="••••••••••••••••" autocomplete="off" maxlength="16" autofocus>
          <div class="input-hint">NIK akan dicocokkan otomatis dengan database DPT Excel panitia.</div>
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label class="form-label" for="namaInput">Nama Warga (Otomatis)</label>
          <input type="text" id="namaInput" class="form-control" readonly
                 placeholder="Terisi otomatis jika NIK terdaftar...">
        </div>

        <button type="button" id="verifyBtn" class="btn btn-primary btn-block">
          🔍 Verifikasi NIK &amp; Lanjut Memilih
        </button>

        <div class="security-note">
          🔒 <b>Aturan Keamanan Sistem:</b><br>
          1. NIK wajib terdaftar pada DPT Excel Warga RT 006.<br>
          2. Sistem menolak pemilih ganda secara otomatis.<br>
          3. Suara bersifat rahasia &amp; dilindungi undang-undang.
        </div>
      </div>

      <!-- KARTU CALON -->
      <div id="votingSection">
        <div class="section-head reveal" style="margin-bottom:2rem; text-align:left;">
          <div class="eyebrow" style="text-align:left;">Langkah 2 — Bilik Suara Digital</div>
          <h2 style="text-align:left;">Pilih Calon Ketua RT 006</h2>
          <p style="margin:0; text-align:left;"><span class="live-dot"></span>Klik kartu calon untuk memilih — kartu terbuka setelah NIK terverifikasi.</p>
        </div>

        <div class="candidates-grid">
          @foreach($candidates as $c)
          <div class="candidate-card reveal" data-delay="{{ $loop->iteration }}"
               data-tilt
               data-id="{{ $c->id }}"
               data-number="{{ $c->number }}"
               data-name="{{ $c->name }}">
            <div class="selected-ring"></div>
            <div class="check-badge">✓</div>
            <div class="no-urut">{{ str_pad($c->number, 2, '0', STR_PAD_LEFT) }}</div>

            @if($c->photo)
              <div class="foto-frame"><img src="{{ asset('storage/' . $c->photo) }}" alt="Foto {{ $c->name }}"></div>
            @else
              <div class="foto-frame"><div class="foto-placeholder">👤</div></div>
            @endif

            <div class="candidate-name">{{ $c->name }}</div>
            <div class="candidate-vision">"{{ $c->vision }}"</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <!-- ================= FITUR ================= -->
  <div class="section" style="padding-top:2rem;">
    <div class="section-head reveal">
      <div class="eyebrow">Mengapa E-Voting?</div>
      <h2>Transparan, Cepat, dan Terpercaya</h2>
    </div>
    <div class="feature-grid">
      <div class="feature-card reveal" data-delay="1">
        <div class="f-icon">🔐</div>
        <h3>Verifikasi NIK KTP</h3>
        <p>Hanya warga terdaftar di DPT Excel panitia yang dapat masuk ke bilik suara digital.</p>
      </div>
      <div class="feature-card reveal" data-delay="2">
        <div class="f-icon">🚫</div>
        <h3>Anti Pemilih Ganda</h3>
        <p>Sistem otomatis mengunci NIK yang sudah memilih — satu warga, satu suara.</p>
      </div>
      <div class="feature-card reveal" data-delay="3">
        <div class="f-icon">🎫</div>
        <h3>Bukti Digital + QR</h3>
        <p>Setiap suara sah menghasilkan tiket bukti memilih berkode QR yang dapat dicetak.</p>
      </div>
      <div class="feature-card reveal" data-delay="4">
        <div class="f-icon">📊</div>
        <h3>Real-Time Monitoring</h3>
        <p>Panitia memantau perolehan suara secara langsung lewat dashboard grafik modern.</p>
      </div>
    </div>
  </div>

  <!-- ================= MODAL KONFIRMASI ================= -->
  <div class="modal-overlay" id="modalConfirm">
    <div class="modal-box">
      <h3 class="modal-title">⚠️ Konfirmasi Pilihan Anda</h3>
      <div class="alert alert-info" style="line-height:1.9;">
        <b>Nama Pemilih:</b> <span id="confirmVoterName">-</span><br>
        <b>NIK:</b> <span id="confirmVoterNik">-</span><br>
        <b>Pilihan:</b> <span id="confirmCandidate" style="color:var(--sky)">-</span>
      </div>
      <p class="muted small" style="line-height:1.7;">
        Pastikan pilihan Anda sudah benar. Setelah dikonfirmasi, suara <b>tidak dapat diubah</b> lagi.
      </p>
      <div class="modal-actions">
        <button type="button" class="btn btn-emerald" id="btnSubmitVote">✅ Ya, Saya Yakin — Simpan Suara</button>
        <button type="button" class="btn btn-ghost" id="btnCancelVote" data-close-modal>↩️ Kembali / Batal</button>
      </div>
    </div>
  </div>

  <!-- ================= MODAL BUKTI MEMILIH ================= -->
  <div class="modal-overlay" id="modalReceipt">
    <div class="modal-box">
      <h3 class="modal-title">🎉 Terima Kasih!</h3>

      <div class="receipt" id="receiptPrint">
        <div class="r-title">✅ SUARA BERHASIL DICATAT</div>
        <div class="r-sub">TANDA BUKTI PEMILIH SAH KETUA RT</div>
        <div class="r-sub" style="color:#0284c7; font-weight:800;">RT 006 / RW 001 JATIWARNA (2026–2031)</div>
        <div class="qr-holder"><div id="qrHolder"></div></div>
        <div class="r-rows">
          <b>Status Suara:</b> <span style="color:#059669; font-weight:800;">TERVERIFIKASI SAH</span><br>
          <b>Nama Pemilih:</b> <span id="rName">-</span><br>
          <b>NIK KTP:</b> <span id="rNik">-</span><br>
          <b>Waktu Voting:</b> <span id="rTime">-</span><br>
          <b>No. Tiket:</b> <span id="rTicket">-</span>
        </div>
        <div style="margin-top:.7rem; font-size:.78rem; color:#475569;">Calon pilihan Anda tercatat secara rahasia dalam sistem.</div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn-primary" id="btnPrintReceipt">🖨️ Cetak / Unduh Bukti (PDF)</button>
        <button type="button" class="btn btn-ghost" data-close-modal>Tutup Window</button>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <div class="footer-brand-line">
      © {{ date('Y') }} <a href="https://digimagine.web.id" target="_blank" rel="noopener" class="footer-brand">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;">
          <path d="M12 2l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.4l-4.8 2.5.9-5.4L4.2 7.7l5.4-.8z"/>
        </svg>
        Digital Imagine Indonesia
      </a> — Seluruh hak cipta dilindungi.
    </div>
    <div class="mt-1">Panitia Pemilihan RT 006 / RW 001 — Kelurahan Jatiwarna, Kec. Pondok Melati, Kota Bekasi</div>
    <div class="muted small mt-1">Aplikasi E-Voting • Laravel 12 • PHP 8.2</div>
  </footer>

  <script src="{{ asset('js/app.js') }}"></script>
  <script src="{{ asset('js/voting.js') }}"></script>
</body>
</html>
