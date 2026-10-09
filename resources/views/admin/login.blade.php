<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Panitia — E-Voting RT 006 Jatiwarna</title>
  @include('partials.head-meta')
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

  <!-- Toggle tema terang/gelap -->
  <button type="button" id="themeToggle" class="theme-toggle" title="Ganti mode terang / gelap" aria-label="Ganti tema">
    <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
  </button>

<div class="login-body">
  <!-- VISUAL KIRI -->
  <div class="login-visual">
    <div class="parallax-layer" data-depth="22">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>
    <div class="hero-grid"></div>
    <div class="inner">
      <img src="{{ asset('img/logo.png') }}" alt="Logo E-Voting RT 006" class="app-logo-big">
      <h2>Panitia Pemilihan<br>RT 006 / RW 001</h2>
      <p>
        Dashboard monitoring hasil e-voting Ketua RT — grafik real-time,
        kelola DPT Excel, dan rekapitulasi suara, dalam satu tempat.
      </p>
      <p class="muted" style="margin-top:1.6rem; font-size:0.78rem; letter-spacing:.12em; text-transform:uppercase;">
        Kelurahan Jatiwarna • Pondok Melati • Kota Bekasi
      </p>
    </div>
  </div>

  <!-- FORM KANAN -->
  <div class="login-form-side">
    <div class="login-card">
      <a href="{{ route('home') }}" class="muted small" style="text-decoration:none;">← Kembali ke halaman voting</a>

      <h1 style="font-size:1.65rem; font-weight:800; margin:1rem 0 .3rem;">Login Dashboard</h1>
      <p class="muted" style="margin:0 0 1.6rem; font-size:.88rem;">
        Area khusus panitia. Masukkan kredensial beserta kode keamanan.
      </p>

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('admin.login.attempt') }}" autocomplete="off">
        @csrf

        <!-- USERNAME -->
        <div style="margin-bottom:1.05rem;">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control"
                 placeholder="Masukkan username panitia" value="{{ old('username') }}" required autofocus>
          @error('username') <div class="alert alert-danger" style="margin-top:.5rem; padding:.5rem .8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- PASSWORD (dengan intip) -->
        <div style="margin-bottom:1.05rem;">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Masukkan password" required>
          @error('password') <div class="alert alert-danger" style="margin-top:.5rem; padding:.5rem .8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- CAPTCHA 5 KARAKTER -->
        <div style="margin-bottom:1.4rem;">
          <label class="form-label" for="captcha">Kode Keamanan (5 Karakter)</label>
          <div class="captcha-row">
            <img src="{{ route('captcha.show') }}" alt="Captcha" class="captcha-img" id="captchaImg">
            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()" title="Ganti kode">↻</button>
            <input type="text" id="captcha" name="captcha" class="form-control"
                   placeholder="Ketik 5 karakter" maxlength="5" required
                   style="text-transform:uppercase; letter-spacing:.28em; font-weight:700;">
          </div>
          @error('captcha') <div class="alert alert-danger" style="margin-top:.5rem; padding:.5rem .8rem;">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">🚀 Masuk Dashboard</button>
      </form>

      <p class="muted small" style="margin-top:1.3rem; line-height:1.7; text-align:center;">
        Akun default: <b>admin</b> / <b>admin123</b> — segera ubah setelah instalasi.
      </p>

      <div style="margin-top:1.6rem; text-align:center; font-size:.78rem; color:#94a3b8;">
        © {{ date('Y') }}
        <a href="https://digimagine.web.id" target="_blank" rel="noopener" class="footer-brand">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;">
            <path d="M12 2l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.4l-4.8 2.5.9-5.4L4.2 7.7l5.4-.8z"/>
          </svg>
          Digital Imagine Indonesia
        </a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
