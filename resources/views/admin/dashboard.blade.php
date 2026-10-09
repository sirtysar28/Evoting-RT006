@extends('admin.layout')

@section('title', 'Dashboard Monitoring Hasil')

@push('head')
  <script async src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')

  <!-- ============ STAT CARDS ============ -->
  <div class="stat-grid">
    <div class="stat-card" style="animation-delay:.05s">
      <div class="glow-line" style="background:linear-gradient(90deg,#38bdf8,#6366f1);"></div>
      <div class="s-icon" style="background:rgba(56,189,248,.14);">✅</div>
      <div class="s-num" id="statVoted">{{ $totalVoted }}</div>
      <div class="s-lbl">Suara Masuk (Sah)</div>
      <div class="s-trend"><span id="lastUpdated">Live</span> • auto-refresh 10 detik</div>
    </div>
    <div class="stat-card" style="animation-delay:.15s">
      <div class="glow-line" style="background:linear-gradient(90deg,#a78bfa,#ec4899);"></div>
      <div class="s-icon" style="background:rgba(167,139,250,.14);">👥</div>
      <div class="s-num" id="statDpt">{{ $totalDpt }}</div>
      <div class="s-lbl">Total DPT Warga</div>
      <div class="s-trend">Sumber: unggahan Excel panitia</div>
    </div>
    <div class="stat-card" style="animation-delay:.25s">
      <div class="glow-line" style="background:linear-gradient(90deg,#fbbf24,#f97316);"></div>
      <div class="s-icon" style="background:rgba(251,191,36,.14);">⏳</div>
      <div class="s-num" id="statNotVoted">{{ $notVoted }}</div>
      <div class="s-lbl">Belum Memilih</div>
      <div class="s-trend">Warga DPT yang belum menggunakan hak pilih</div>
    </div>
    <div class="stat-card" style="animation-delay:.35s">
      <div class="glow-line" style="background:linear-gradient(90deg,#34d399,#10b981);"></div>
      <div class="s-icon" style="background:rgba(52,211,153,.14);">📈</div>
      <div class="s-num" id="statParticipation">{{ $participation }}%</div>
      <div class="s-lbl">Partisipasi Pemilih</div>
      <div class="result-track mt-1"><div class="result-fill" id="participationTrack"
        style="width:{{ $participation }}%; background:linear-gradient(90deg,#34d399,#38bdf8);"></div></div>
    </div>
  </div>

  <!-- ============ QUICK COUNT ============ -->
  <div class="panel mb-3" style="animation:fadeUp .7s var(--ease) .4s both;">
    <div class="panel-head">
      <div>
        <h3>⚡ Quick Count — Perolehan Suara Real-Time</h3>
        <div class="sub" id="leadingInfo">Menghitung...</div>
      </div>
      <span class="badge badge-success"><span class="live-dot" style="background:var(--emerald);"></span>LIVE</span>
    </div>
    <div id="quickCount">
      @foreach($candidates as $c)
      <div class="result-bar-row">
        <div class="r-top">
          <span>No. {{ str_pad($c->number, 2, '0', STR_PAD_LEFT) }} — {{ $c->name }}</span>
          <span>{{ $c->voters_count }} suara • {{ $c->percent }}%</span>
        </div>
        <div class="result-track">
          <div class="result-fill" style="width:{{ $c->percent }}%; background:linear-gradient(90deg, {{ $c->color }}, {{ $c->color }}77);"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- ============ CHARTS ============ -->
  <div class="panel-grid">
    <div class="panel" style="animation:fadeUp .7s var(--ease) .5s both;">
      <div class="panel-head">
        <div>
          <h3>🍩 Diagram Distribusi Suara</h3>
          <div class="sub">Persentase perolehan suara per calon</div>
        </div>
      </div>
      <div class="chart-holder"><canvas id="chartDoughnut"></canvas></div>
    </div>

    <div class="panel" style="animation:fadeUp .7s var(--ease) .6s both;">
      <div class="panel-head">
        <div>
          <h3>📊 Grafik Batang Perolehan Suara</h3>
          <div class="sub">Perbandingan jumlah suara antar calon</div>
        </div>
      </div>
      <div class="chart-holder"><canvas id="chartBar"></canvas></div>
    </div>
  </div>

  <div class="panel mb-3" style="animation:fadeUp .7s var(--ease) .7s both;">
    <div class="panel-head">
      <div>
        <h3>📉 Tren Suara Masuk per Jam</h3>
        <div class="sub">Aktivitas pemilih dalam 24 jam terakhir</div>
      </div>
    </div>
    <div class="chart-holder-sm"><canvas id="chartTrend"></canvas></div>
  </div>

  <!-- ============ PEMILIH TERBARU ============ -->
  <div class="table-wrap" style="animation:fadeUp .7s var(--ease) .8s both;">
    <div class="panel-head" style="padding:1.3rem 1.4rem .6rem;">
      <h3>🕒 Riwayat Pemilih Terbaru</h3>
      <a href="{{ route('admin.dpt.index') }}" class="btn btn-ghost btn-sm">Lihat semua DPT →</a>
    </div>
    <div class="table-scroll">
      <table class="tbl">
        <thead>
          <tr><th>Warga</th><th>Pilihan</th><th>Waktu</th><th>Status</th></tr>
        </thead>
        <tbody id="recentBody">
          @forelse($recent as $v)
          <tr>
            <td><b>{{ $v->name }}</b><br><span class="muted small">{{ substr($v->nik,0,6) }}******{{ substr($v->nik,12) }}</span></td>
            <td>No. {{ str_pad($v->candidate?->number ?? 0, 2, '0', STR_PAD_LEFT) }} — {{ $v->candidate?->name ?? '-' }}</td>
            <td>{{ $v->voted_at?->translatedFormat('d M Y, H:i') }}</td>
            <td><span class="badge badge-success">SAH</span></td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center muted" style="padding:2rem;">Belum ada warga yang memilih</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Data awal untuk JS -->
  <script>
    window.__STATS__ = {!! $chartData !!};
  </script>
@endsection

@push('scripts')
  <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush
