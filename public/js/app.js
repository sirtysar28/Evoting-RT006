/* ============================================================
   E-VOTING RT006 — Core JS
   Password peek • Toast • Reveal on scroll • Parallax • Counter
   • Theme Toggle (terang/gelap)
   ============================================================ */

/* ---------- THEME TOGGLE (Light/Dark) — default LIGHT ---------- */
document.addEventListener('DOMContentLoaded', () => {
  // Bisa banyak tombol: #themeToggle (fixed) + [data-theme-toggle] (inline)
  const btns = [...document.querySelectorAll('#themeToggle, [data-theme-toggle]')];
  if (!btns.length) return;

  btns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const html = document.documentElement;
      const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      applyTheme(next);

      btn.classList.add('spin-once');
      setTimeout(() => btn.classList.remove('spin-once'), 600);
    });
  });
});

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  try { localStorage.setItem('evoting-theme', theme); } catch { /* private mode */ }

  // Sinkronkan warna status-bar browser/mobile (PWA)
  const meta = document.querySelector('meta[name="theme-color"]');
  if (meta) meta.setAttribute('content', theme === 'dark' ? '#070b14' : '#eef2f8');
}

/* ---------- PWA: Register Service Worker ---------- */
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => { /* offline dev, abaikan */ });
  });
}

/* ---------- TOAST ---------- */
function showToast(title, message, type = 'info', duration = 4500) {
  const icons = { success: '✅', error: '⛔', info: 'ℹ️' };
  let wrap = document.querySelector('.toast-wrap');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    document.body.appendChild(wrap);
  }
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `
    <div class="t-icon">${icons[type] || 'ℹ️'}</div>
    <div>
      <div class="t-title">${title}</div>
      <div class="t-msg">${message}</div>
    </div>`;
  wrap.appendChild(el);
  setTimeout(() => {
    el.classList.add('out');
    setTimeout(() => el.remove(), 400);
  }, duration);
}

/* ---------- PASSWORD PEEK (intip password) ----------
   Otomatis aktif di SEMUA input password */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type="password"]').forEach(attachPeek);

  // Observasi input password yang ditambahkan belakangan (mis. via AJAX)
  const observer = new MutationObserver(mutations => {
    mutations.forEach(m => m.addedNodes.forEach(node => {
      if (node.nodeType !== 1) return;
      if (node.matches?.('input[type="password"]')) attachPeek(node);
      node.querySelectorAll?.('input[type="password"]').forEach(attachPeek);
    }));
  });
  observer.observe(document.body, { childList: true, subtree: true });
});

function attachPeek(input) {
  if (input.dataset.peekAttached) return;
  input.dataset.peekAttached = '1';

  // Bungkus jika belum dalam wrapper
  let wrap = input.parentElement;
  if (!wrap.classList.contains('pw-wrap')) {
    wrap = document.createElement('div');
    wrap.className = 'pw-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
  }

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'pw-peek';
  btn.setAttribute('aria-label', 'Lihat password');
  btn.innerHTML = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  btn.addEventListener('click', () => {
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.innerHTML = showing
      ? '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
      : '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    btn.setAttribute('aria-label', showing ? 'Lihat password' : 'Sembunyikan password');
    input.focus();
  });
  wrap.appendChild(btn);
}

/* ---------- REVEAL ON SCROLL ---------- */
document.addEventListener('DOMContentLoaded', () => {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
});

/* ---------- PARALLAX (mouse + scroll) ---------- */
document.addEventListener('DOMContentLoaded', () => {
  // Mouse parallax untuk layer ber-atribut data-depth
  const layers = document.querySelectorAll('[data-depth]');
  let raf = null;

  document.addEventListener('mousemove', e => {
    if (window.innerWidth < 768) return;
    if (raf) return;
    raf = requestAnimationFrame(() => {
      const cx = (e.clientX / window.innerWidth - 0.5);
      const cy = (e.clientY / window.innerHeight - 0.5);
      layers.forEach(layer => {
        const depth = parseFloat(layer.dataset.depth || 20);
        layer.style.transform = `translate3d(${cx * depth}px, ${cy * depth}px, 0)`;
      });
      raf = null;
    });
  });

  // Scroll parallax hero content
  const heroContent = document.querySelector('.hero-content[data-parallax]');
  if (heroContent) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      if (y < window.innerHeight) {
        heroContent.style.transform = `translateY(${y * 0.28}px)`;
        heroContent.style.opacity = String(Math.max(0, 1 - y / (window.innerHeight * 0.85)));
      }
    }, { passive: true });
  }
});

/* ---------- ANIMATED COUNTER ---------- */
function animateCounter(el, target, duration = 1600, suffix = '') {
  const start = performance.now();
  const from = 0;
  function tick(now) {
    const p = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
    el.textContent = Math.round(from + (target - from) * eased).toLocaleString('id-ID') + suffix;
    if (p < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
}

document.addEventListener('DOMContentLoaded', () => {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      animateCounter(el, parseInt(el.dataset.count || '0', 10), 1600, el.dataset.suffix || '');
      io.unobserve(el);
    });
  }, { threshold: 0.4 });
  document.querySelectorAll('[data-count]').forEach(el => io.observe(el));
});

/* ---------- 3D TILT CARD ---------- */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-tilt]').forEach(card => {
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const rx = ((e.clientY - r.top) / r.height - 0.5) * -10;
      const ry = ((e.clientX - r.left) / r.width - 0.5) * 10;
      card.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translateY(-6px)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });
});
