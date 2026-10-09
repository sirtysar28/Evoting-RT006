/* ============================================================
   E-VOTING RT 006 Jatiwarna — Service Worker (PWA)
   Strategi:
   • Static assets  → cache-first (cepat, offline-ready)
   • Navigasi (HTML)→ network-first, fallback cache, lalu '/'
   • API dinamis (verify-nik, vote, admin/*, captcha) → network only
   ============================================================ */

const CACHE_NAME = 'evoting-rt006-v2';

const PRECACHE = [
  '/',
  '/leaderboard',
  '/css/app.css',
  '/js/app.js',
  '/js/voting.js',
  '/js/admin.js',
  '/js/admin-dashboard.js',
  '/js/leaderboard.js',
  '/img/logo.png',
  '/img/icons/icon-192.png',
  '/img/icons/icon-512.png',
  '/manifest.webmanifest',
];

/* Rute yang TIDAK boleh di-cache (data live / keamanan) */
const NEVER_CACHE = [
  '/verify-nik',
  '/vote',
  '/cancel-verification',
  '/captcha',
  '/leaderboard/live',
  '/admin/dashboard/live',
];

/* ---------- Install: precache aset inti ---------- */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(PRECACHE))
      .then(() => self.skipWaiting())
  );
});

/* ---------- Activate: bersihkan cache lama ---------- */
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

/* ---------- Fetch ---------- */
self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Hanya GET
  if (request.method !== 'GET' || request.url.includes('browser-sync')) return;

  const url = new URL(request.url);

  // API dinamis & rute sensitif → selalu jaringan
  if (NEVER_CACHE.some((path) => url.pathname.startsWith(path))) return;
  if (url.pathname.startsWith('/admin') && request.mode === 'navigate') return;

  // Navigasi halaman → network-first
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((c) => c.put(request, copy)).catch(() => {});
          return res;
        })
        .catch(() =>
          caches.match(request).then((cached) => cached || caches.match('/'))
        )
    );
    return;
  }

  // Aset statis (same-origin & CDN font/js) → cache-first
  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) return cached;
      return fetch(request).then((res) => {
        // Simpan hanya respons valid & same-origin / font CDN
        if (res.ok || res.type === 'opaque') {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((c) => c.put(request, copy)).catch(() => {});
        }
        return res;
      }).catch(() => cached);
    })
  );
});
