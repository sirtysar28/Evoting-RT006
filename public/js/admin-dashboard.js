/* ============================================================
   E-VOTING RT006 — Admin Dashboard
   Chart.js: Doughnut • Bar • Line + Live auto-refresh
   Render DOM langsung; Chart.js digambar saat CDN siap (async).
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  const ctxDoughnut = document.getElementById('chartDoughnut');
  const ctxBar      = document.getElementById('chartBar');
  const ctxLine     = document.getElementById('chartTrend');
  if (!ctxDoughnut) return;

  const palette = ['#38bdf8', '#a78bfa', '#34d399', '#fbbf24', '#fb7185', '#22d3ee'];
  let doughnut = null, bar = null, line = null;
  let chartReady = false;
  let lastStats = null;

  applyStats(window.__STATS__);
  waitForChart();
  setInterval(poll, 10000);

  async function poll() {
    try {
      const res = await fetch('/admin/dashboard/live', { headers: { Accept: 'application/json' } });
      if (!res.ok) return;
      applyStats(await res.json());
    } catch { /* silent */ }
  }

  function applyStats(stats) {
    if (!stats) return;
    lastStats = stats;
    renderDom(stats);
    if (chartReady) drawCharts(stats);
  }

  function waitForChart() {
    if (typeof Chart === 'undefined') {
      setTimeout(waitForChart, 150);
      return;
    }
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(148,163,184,0.08)';
    chartReady = true;
    if (lastStats) drawCharts(lastStats);
  }

  function gradientFill(ctx, color) {
    const g = ctx.createLinearGradient(0, 0, 0, 280);
    g.addColorStop(0, color + 'cc');
    g.addColorStop(1, color + '11');
    return g;
  }

  /* ---------------- RENDER DOM (stat, quick count, recent) ---------------- */
  function renderDom(stats) {
    /* --- Stat cards --- */
    updateNum('statVoted', stats.totalVoted);
    updateNum('statDpt', stats.totalDpt);
    updateNum('statNotVoted', stats.notVoted);
    const p = document.getElementById('statParticipation');
    if (p) p.textContent = stats.participation + '%';
    document.getElementById('participationTrack').style.width = stats.participation + '%';

    /* --- Quick count bars --- */
    const qc = document.getElementById('quickCount');
    qc.innerHTML = stats.candidates.map((c, i) => `
      <div class="result-bar-row">
        <div class="r-top">
          <span>No. ${String(c.number).padStart(2, '0')} — ${c.name}</span>
          <span>${c.voters_count} suara • ${c.percent}%</span>
        </div>
        <div class="result-track">
          <div class="result-fill" data-w="${c.percent}" style="background: linear-gradient(90deg, ${c.color || palette[i]}, ${(c.color || palette[i])}77)"></div>
        </div>
      </div>`).join('');
    requestAnimationFrame(() =>
      qc.querySelectorAll('.result-fill').forEach(f => f.style.width = f.dataset.w + '%'));

    /* --- Leading info --- */
    const lead = document.getElementById('leadingInfo');
    if (stats.leading && stats.totalVoted > 0) {
      lead.innerHTML = `🏆 Sementara memimpin: <b>${stats.leading.name}</b> dengan <b>${stats.leading.voters_count}</b> suara`;
    } else if (stats.totalVoted > 0) {
      lead.textContent = '🤝 Sementara imbang!';
    } else {
      lead.textContent = 'Belum ada suara masuk';
    }

    /* --- Recent voters --- */
    const tbody = document.getElementById('recentBody');
    if (stats.recent.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center muted" style="padding:2rem">Belum ada warga yang memilih</td></tr>`;
    } else {
      tbody.innerHTML = stats.recent.map(v => `
        <tr>
          <td><b>${v.name}</b><br><span class="muted small">${maskNik(v.nik)}</span></td>
          <td>No. ${String(v.candidate?.number ?? '-').padStart(2, '0')} — ${v.candidate?.name ?? '-'}</td>
          <td>${v.voted_at}</td>
          <td><span class="badge badge-success">SAH</span></td>
        </tr>`).join('');
    }

    document.getElementById('lastUpdated').textContent =
      'Diperbarui: ' + new Date().toLocaleTimeString('id-ID');
  }

  function updateNum(id, val) {
    const el = document.getElementById(id);
    if (el && el.dataset.val !== String(val)) {
      el.dataset.val = val;
      animateCounter(el, val, 900);
    }
  }
  const maskNik = nik => nik.substring(0, 6) + '******' + nik.substring(12);

  /* ---------------- CHARTS ---------------- */
  function drawCharts(stats) {
    const labels   = stats.candidates.map(c => `No. ${String(c.number).padStart(2, '0')} — ${c.name}`);
    const votes    = stats.candidates.map(c => c.voters_count);
    const colors   = stats.candidates.map((c, i) => c.color || palette[i % palette.length]);
    const percents = stats.candidates.map(c => c.percent);

    /* --- Doughnut: distribusi suara --- */
    doughnut?.destroy();
    doughnut = new Chart(ctxDoughnut, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: votes,
          backgroundColor: colors.map(c => c + 'd9'),
          borderColor: 'rgba(7,11,20,0.9)',
          borderWidth: 5,
          hoverOffset: 14,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '66%',
        animation: { animateRotate: true, duration: 1200, easing: 'easeOutQuart' },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11, weight: '600' } },
          },
          tooltip: {
            backgroundColor: 'rgba(15,23,42,0.95)',
            padding: 12,
            cornerRadius: 10,
            callbacks: {
              label: ctx => ` ${ctx.parsed} suara (${percents[ctx.dataIndex] ?? 0}%)`,
            },
          },
        },
      },
    });

    /* --- Bar: perbandingan per calon --- */
    bar?.destroy();
    bar = new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: stats.candidates.map(c => `No. ${String(c.number).padStart(2, '0')}`),
        datasets: [{
          label: 'Jumlah Suara',
          data: votes,
          backgroundColor: colors.map(c => gradientFill(ctxBar.getContext('2d'), c)),
          borderColor: colors,
          borderWidth: 2,
          borderRadius: 12,
          maxBarThickness: 86,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1100, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(15,23,42,0.95)',
            padding: 12,
            cornerRadius: 10,
            callbacks: { label: ctx => ` ${ctx.parsed.y} suara` },
          },
        },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { drawBorder: false } },
          x: { grid: { display: false } },
        },
      },
    });

    /* --- Line: tren suara per jam --- */
    line?.destroy();
    line = new Chart(ctxLine, {
      type: 'line',
      data: {
        labels: Object.keys(stats.trendData).map(h => `${h}:00`),
        datasets: [{
          label: 'Suara Masuk',
          data: Object.values(stats.trendData),
          borderColor: '#38bdf8',
          backgroundColor: gradientFill(ctxLine.getContext('2d'), '#38bdf8'),
          fill: true,
          tension: 0.45,
          borderWidth: 2.5,
          pointBackgroundColor: '#38bdf8',
          pointRadius: 3,
          pointHoverRadius: 7,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1000, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 12, cornerRadius: 10 },
        },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { drawBorder: false } },
          x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } },
        },
      },
    });
  }
});
