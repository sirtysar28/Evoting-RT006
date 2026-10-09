<meta name="theme-color" content="#eef2f8">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/icons/favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/icons/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/icons/apple-touch-icon.png') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="E-Voting RT006">

{{-- Terapkan tema SEBELUM CSS dimuat (anti-flash): default TERANG --}}
<script>
  (function () {
    var t = 'light';
    try { t = localStorage.getItem('evoting-theme') || 'light'; } catch (e) {}
    document.documentElement.setAttribute('data-theme', t === 'dark' ? 'dark' : 'light');
    var m = document.querySelector('meta[name="theme-color"]');
    if (m) m.setAttribute('content', t === 'dark' ? '#070b14' : '#eef2f8');
  })();
</script>
