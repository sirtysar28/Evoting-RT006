<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') — Panitia E-Voting RT 006</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @include('partials.head-meta')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"></noscript>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head')
</head>
<body>

<div class="admin-body">
  <!-- ============ SIDEBAR ============ -->
  <aside class="admin-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
      <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo">
      <div>
        <div class="t1">E-Voting RT 006</div>
        <div class="t2">Panitia Jatiwarna</div>
      </div>
    </a>

    <a href="{{ route('admin.dashboard') }}"
       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <span class="ic">📊</span> Dashboard Hasil
    </a>
    <a href="{{ route('admin.dpt.index') }}"
       class="nav-link {{ request()->routeIs('admin.dpt.*') ? 'active' : '' }}">
      <span class="ic">👥</span> Kelola DPT Warga
    </a>
    <a href="{{ route('admin.candidates.index') }}"
       class="nav-link {{ request()->routeIs('admin.candidates.*') ? 'active' : '' }}">
      <span class="ic">🧑‍💼</span> Data Calon
    </a>

    <a href="{{ route('leaderboard') }}" target="_blank" rel="noopener"
       class="nav-link {{ request()->routeIs('leaderboard*') ? 'active' : '' }}">
      <span class="ic">🏆</span> Leaderboard Publik
    </a>

    <div class="sidebar-footer">
      <a href="{{ route('home') }}" class="nav-link">
        <span class="ic">🌐</span> Lihat Halaman Voting
      </a>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="nav-link" style="width:100%; background:none; cursor:pointer; text-align:left; font-family:inherit;">
          <span class="ic">🚪</span> Keluar
        </button>
      </form>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>@yield('title', 'Dashboard')</h1>
        <div class="crumb">RT 006 / RW 001 Jatiwarna • Masa Bakti 2026–2031</div>
      </div>
      <div class="flex-between">
        <span class="badge badge-muted">👤 {{ session('admin_name', 'Panitia') }}</span>
        <div style="display:flex; gap:.55rem; align-items:center;">
          @yield('topbar-actions')
          <button type="button" class="theme-toggle inline" data-theme-toggle title="Ganti mode terang / gelap" aria-label="Ganti tema">
            <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success" style="margin-bottom:1.2rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger" style="margin-bottom:1.2rem;">{{ session('error') }}</div>
    @endif

    @yield('content')
  </main>

  <footer class="admin-footer">
    {{ date('Y') }} © Developed by <a href="https://digimagine.web.id" target="_blank" rel="noopener" class="footer-brand">
      Digital Imagine Indonesia
    </a> — Seluruh hak cipta dilindungi.
  </footer>
</div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
