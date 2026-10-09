/* ============================================================
   E-VOTING RT006 — Leaderboard Publik
   Podium • Ranking + persen • Grafik live • Confetti
   Catatan: render DOM jalan langsung; Chart.js digambar
   terpisah saat library CDN selesai dimuat (async).
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  const podiumEl = document.getElementById('podium');
  const rankList = document.getElementById('rankList');
  if (!podiumEl || !rankList) return;

  const palette = ['#38bdf8', '#a78bfa', '#34d399', '#fbbf24', '#fb7185', '#22d3ee'];
  let doughnut = null, trend = null;
  let confettiDone = false;
  let lastState = null;
  let chartReady = false;

  applyState(window.__LB__);
  setInterval(poll, 10000);
  waitForChart();

  /* ---------------- LIVE POLLING ---------------- */
  async function poll() {
    try {
      const res = await fetch('/leaderboard/live', { headers: { Accept: 'application/json' } });
      if (!res.ok) return;
      applyState(await res.json());
    } catch { /* silent */ }
  }

  function applyState(s) {
    if (!s) return;
    lastState = s;
    renderStats(s);
    renderPodium(s);
    renderRanks(s);
    maybeConfetti(s);
    if (chartReady) drawCharts(s);
  }

  /* ---------------- CHART.JS (async, non-blocking) ---------------- */
  function waitForChart() {
    if (typeof Chart === 'undefined') {
      setTimeout(waitForChart, 150);
      return;
    }
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(148,163,184,0.08)';
    chartReady = true;
    if (lastState) drawCharts(lastState);
  }

  /* ---------------- HELPERS (function declaration: hoisted) ---------------- */
  function pad(n) { return String(n).padStart(2, '0'); }

  function medal(i) {
    return ['🥇 Peringkat 1', '🥈 Peringkat 2', '🥉 Peringkat 3'][i] ?? `#${i + 1}`;
  }

  function setNum(id, val) {
    const el = document.getElementById(id);
    if (el && el.dataset.val !== String(val)) {
      el.dataset.val = val;
      animateCounter(el, val, 900);
    }
  }

  /* ---------------- STAT CHIPS ---------------- */
  function renderStats(s) {
    setNum('lbVoted', s.totalVoted);
    setNum('lbDpt', s.totalDpt);
    setNum('lbNotVoted', s.notVoted);
    const p = document.getElementById('lbParticipation');
    if (p) p.textContent = s.participation + '%';
    const bar = document.getElementById('lbPartBar');
    if (bar) bar.style.width = s.participation + '%';
    const upd = document.getElementById('lbUpdatedAt');
    if (upd) upd.textContent = s.updatedAt;
  }

  /* ---------------- PODIUM ---------------- */
  function renderPodium(s) {
    const hasVotes = s.totalVoted > 0;
    const top = s.candidates.slice(0, 3);
    const orderPos = [2, 1, 3]; // urutan visual: peringkat 2, 1, 3

    let html = top.map((c, i) => `
      <div class="podium-col" data-pos="${orderPos[i]}" style="--pc:${c.color || palette[i]}">
        <div class="podium-photo">
          ${i === 0 && hasVotes ? '<span class="crown">👑</span>' : ''}
          ${c.photo ? `<img src="${c.photo}" alt="${c.name}">` : '👤'}
        </div>
        <div><span class="podium-medal">${medal(i)}</span></div>
        <div class="podium-name">No. ${pad(c.number)} — ${c.name}</div>
        <div class="podium-pct">${c.percent}%</div>
        <div class="podium-votes">${c.votes.toLocaleString('id-ID')} suara</div>
        <div class="podium-base"><span class="pct-in">${c.votes.toLocaleString('id-ID')}</span></div>
      </div>`).join('');

    if (top.length < 3) {
      html += `
      <div class="podium-col" data-pos="3">
        <div class="podium-photo">🪑</div>
        <div><span class="podium-medal">—</span></div>
        <div class="podium-name muted">Slot peringkat 3</div>
        <div class="podium-votes">menunggu calon lain</div>
        <div class="podium-base" style="--pc:#94a3b8; opacity:.35"><span class="pct-in">–</span></div>
      </div>`;
    }

    podiumEl.innerHTML = html;
  }

  /* ---------------- RANKING ROWS ---------------- */
  function renderRanks(s) {
    const hasVotes = s.totalVoted > 0;

    rankList.innerHTML = s.candidates.map((c, i) => `
      <div class="rank-row ${i === 0 && hasVotes ? 'first' : ''}" data-rank="${i + 1}" style="animation-delay:${i * 0.08}s">
        <div class="rank-badge">${i + 1}</div>
        <div class="rank-avatar">${c.photo ? `<img src="${c.photo}" alt="">` : '👤'}</div>
        <div class="rank-info">
          <div class="r-name">No. ${pad(c.number)} — ${c.name}</div>
          <div class="r-sub">"${c.vision ?? ''}"</div>
          <div class="rank-track mt-1"><div class="rank-fill"
            style="background: linear-gradient(90deg, ${c.color || palette[i]}, ${(c.color || palette[i])}99);"
            data-w="${c.percent}"></div></div>
        </div>
        <div class="rank-right">
          <div class="r-pct" style="color:${i === 0 && hasVotes ? '#d97706' : 'var(--text)'}">${c.percent}%</div>
          <div class="r-votes">${c.votes.toLocaleString('id-ID')} suara</div>
        </div>
      </div>`).join('');

    requestAnimationFrame(() =>
      rankList.querySelectorAll('.rank-fill').forEach(f => { f.style.width = f.dataset.w + '%'; }));

    document.getElementById('lbEmpty').style.display = hasVotes ? 'none' : 'block';
  }

  /* ---------------- CONFETTI TRIGGER ---------------- */
  function maybeConfetti(s) {
    const leader = s.candidates[0];
    if (!confettiDone && s.totalVoted > 0 && leader.percent > 50) {
      confetti(leader.color);
      confettiDone = true;
    }
  }

  /* ---------------- CHARTS ---------------- */
  function gradientFill(ctx, color) {
    const g = ctx.createLinearGradient(0, 0, 0, 240);
    g.addColorStop(0, color + 'cc');
    g.addColorStop(1, color + '11');
    return g;
  }

  function drawCharts(s) {
    const el1 = document.getElementById('lbChartDoughnut');
    if (el1) {
      doughnut?.destroy();
      doughnut = new Chart(el1, {
        type: 'doughnut',
        data: {
          labels: s.candidates.map(c => `No. ${pad(c.number)} — ${c.name}`),
          datasets: [{
            data: s.candidates.map(c => c.votes),
            backgroundColor: s.candidates.map((c, i) => (c.color || palette[i]) + 'd9'),
            borderColor: 'rgba(127,140,170,0.25)',
            borderWidth: 4,
            hoverOffset: 14,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '64%',
          animation: { animateRotate: true, duration: 1100, easing: 'easeOutQuart' },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { usePointStyle: true, pointStyle: 'circle', padding: 14, font: { size: 11, weight: '600' } },
            },
            tooltip: {
              backgroundColor: 'rgba(15,23,42,0.95)', padding: 12, cornerRadius: 10,
              callbacks: { label: ctx => ` ${ctx.parsed} suara (${s.candidates[ctx.dataIndex].percent}%)` },
            },
          },
        },
      });
    }

    const el2 = document.getElementById('lbChartTrend');
    if (el2) {
      trend?.destroy();
      trend = new Chart(el2, {
        type: 'line',
        data: {
          labels: Object.keys(s.trendData).map(h => `${h}:00`),
          datasets: [{
            label: 'Suara Masuk',
            data: Object.values(s.trendData),
            borderColor: '#34d399',
            backgroundColor: gradientFill(el2.getContext('2d'), '#34d399'),
            fill: true, tension: 0.45, borderWidth: 2.5,
            pointBackgroundColor: '#34d399', pointRadius: 3, pointHoverRadius: 7,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: { duration: 900, easing: 'easeOutQuart' },
          plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 12, cornerRadius: 10 },
          },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { drawBorder: false } },
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
          },
        },
      });
    }
  }

  /* ---------------- CONFETTI (tanpa library) ---------------- */
  function confetti(accentColor = '#fbbf24') {
    const canvas = document.getElementById('confettiCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = innerWidth;
    canvas.height = innerHeight;

    const colors = ['#fbbf24', '#38bdf8', '#a78bfa', '#34d399', '#fb7185', accentColor];
    const pieces = Array.from({ length: 140 }, () => ({
      x: Math.random() * canvas.width,
      y: -20 - Math.random() * canvas.height * 0.4,
      w: 6 + Math.random() * 7,
      h: 8 + Math.random() * 8,
      vy: 2.2 + Math.random() * 3.2,
      vx: -1.4 + Math.random() * 2.8,
      rot: Math.random() * Math.PI,
      vr: -0.12 + Math.random() * 0.24,
      color: colors[(Math.random() * colors.length) | 0],
    }));

    const t0 = performance.now();
    (function frame(now) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      pieces.forEach(p => {
        p.x += p.vx; p.y += p.vy; p.rot += p.vr; p.vy += 0.02;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.fillStyle = p.color;
        ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
        ctx.restore();
      });
      if (now - t0 < 4200) requestAnimationFrame(frame);
      else ctx.clearRect(0, 0, canvas.width, canvas.height);
    })(t0);
  }
});
