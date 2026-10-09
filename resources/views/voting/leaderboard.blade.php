<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leaderboard Hasil Suara — E-Voting RT 006 Jatiwarna</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @include('partials.head-meta')

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"></noscript>

  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script async src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>

  <!-- Toggle tema -->
  <button type="button" id="themeToggle" class="theme-toggle" title="Ganti mode terang / gelap" aria-label="Ganti tema">
    <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
  </button>

  <!-- ============ TOP NAV ============ -->
  <nav class="lb-topnav">
    <a href="{{ route('home') }}" class="lb-brand">
      <img src="{{ asset('img/logo.png') }}" alt="Logo">
      <span>E-Voting RT 006<small>Leaderboard Jatiwarna</small></span>
    </a>
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm">🗳️ Halaman Voting</a>
  </nav>

  <!-- ============ HERO KECIL ============ -->
  <section class="hero" style="min-height: 62vh;">
    <div class="parallax-layer" data-depth="24">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>
    <div class="parallax-layer" data-depth="44">
      <span class="float-icon" style="top:20%; left:12%;">🏆</span>
      <span class="float-icon" style="top:26%; right:14%; animation-delay:-2s;">📊</span>
      <span class="float-icon" style="bottom:20%; left:18%; animation-delay:-4s;">🥇</span>
      <span class="float-icon" style="bottom:26%; right:16%; animation-delay:-1s;">⚡</span>
    </div>
    <div class="hero-grid"></div>

    <div class="hero-content" data-parallax>
      <span class="hero-badge"><span class="dot"></span> Hasil Real-Time • Live</span>
      <h1 class="hero-title" style="font-size:clamp(1.9rem,5vw,3.4rem);">Leaderboard<br>Pemilihan Ketua RT 006</h1>
      <p class="hero-subtitle">
        Pantau perolehan suara calon ketua RT 006 / RW 001 Jatiwarna secara
        <b>langsung &amp; transparan</b>. Data diperbarui otomatis setiap 10 detik.
      </p>

      <div class="hero-stats">
        <div class="stat-chip">
          <div class="num" id="lbVoted">{{ $totalVoted }}</div>
          <div class="lbl">Total Suara Masuk</div>
        </div>
        <div class="stat-chip">
          <div class="num" id="lbParticipation">{{ $participation }}%</div>
          <div class="lbl">Partisipasi Pemilih</div>
          <div class="result-track mt-1" style="height:8px;">
            <div class="result-fill" id="lbPartBar" style="width:{{ $participation }}%; background:linear-gradient(90deg,#34d399,#38bdf8);"></div>
          </div>
        </div>
        <div class="stat-chip">
          <div class="num" id="lbDpt">{{ $totalDpt }}</div>
          <div class="lbl">Warga DPT</div>
        </div>
        <div class="stat-chip">
          <div class="num" id="lbNotVoted">{{ $notVoted }}</div>
          <div class="lbl">Belum Memilih</div>
        </div>
      </div>

      <p class="live-refresh" style="margin-top:1.6rem; animation:fadeUp 1s var(--ease) .6s both;">
        <span class="live-ring"></span> Live • diperbarui <span id="lbUpdatedAt">{{ $updatedAt }}</span>
      </p>
    </div>
  </section>

  <!-- ============ PODIUM ============ -->
  <div class="section" style="padding-bottom:0;">
    <div class="section-head reveal">
      <div class="eyebrow">🏆 Podium Peringkat</div>
      <h2>Sementara Memimpin</h2>
      <p>Peringkat berdasarkan jumlah suara sah yang masuk saat ini.</p>
    </div>
    <div class="podium" id="podium"></div>
  </div>

  <!-- ============ RANKING ============ -->
  <div class="section" style="padding-top:2.5rem;">
    <div class="section-head reveal">
      <div class="eyebrow">📊 Klasemen Lengkap</div>
      <h2>Perolehan Suara per Calon</h2>
    </div>

    <div class="alert alert-info" id="lbEmpty" style="display:none;">
      ⏳ Belum ada suara masuk. Leaderboard akan terisi begitu warga mulai memilih.
    </div>

    <div class="rank-list" id="rankList"></div>
  </div>

  <!-- ============ GRAFIK ============ -->
  <div class="section" style="padding-top:1rem;">
    <div class="panel-grid">
      <div class="panel reveal" data-delay="1">
        <div class="panel-head">
          <div>
            <h3>🍩 Distribusi Suara</h3>
            <div class="sub">Persentase perolehan suara per calon</div>
          </div>
        </div>
        <div class="chart-holder"><canvas id="lbChartDoughnut"></canvas></div>
      </div>
      <div class="panel reveal" data-delay="2">
        <div class="panel-head">
          <div>
            <h3>📈 Tren Suara Masuk</h3>
            <div class="sub">Aktivitas pemilih 24 jam terakhir</div>
          </div>
        </div>
        <div class="chart-holder"><canvas id="lbChartTrend"></canvas></div>
      </div>
    </div>
  </div>

  <!-- ============ CTA ============ -->
  <div class="section" style="padding-top:0;">
    <div class="glass-card reveal text-center">
      <h3 style="margin:0 0 .5rem; font-size:1.15rem;">Belum menggunakan hak pilih?</h3>
      <p class="muted" style="margin:0 0 1.3rem; font-size:.9rem;">Satu warga satu suara — pastikan suara Anda ikut tercatat!</p>
      <a href="{{ route('home') }}#verifikasi" class="btn btn-primary">🗳️ Verifikasi NIK &amp; Pilih Sekarang</a>
    </div>
  </div>

  <canvas id="confettiCanvas"></canvas>

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

  <script>window.__LB__ = {!! $json !!};</script>
  <script src="{{ asset('js/app.js') }}"></script>
  <script src="{{ asset('js/leaderboard.js') }}"></script>
</body>
</html>
