@extends('admin.layout')

@section('title', 'Kelola DPT Warga')

@section('topbar-actions')
  <a href="{{ route('admin.dpt.template') }}" class="btn btn-ghost btn-sm">📄 Unduh Template</a>
  <a href="{{ route('admin.dpt.export') }}" class="btn btn-ghost btn-sm">📤 Export Excel</a>
@endsection

@section('content')

  <!-- ============ SUMMARY ============ -->
  <div class="stat-grid">
    <div class="stat-card" style="animation-delay:.05s">
      <div class="glow-line" style="background:linear-gradient(90deg,#38bdf8,#6366f1);"></div>
      <div class="s-icon" style="background:rgba(56,189,248,.14);">👥</div>
      <div class="s-num">{{ number_format($summary['total'], 0, ',', '.') }}</div>
      <div class="s-lbl">Total Warga DPT</div>
    </div>
    <div class="stat-card" style="animation-delay:.15s">
      <div class="glow-line" style="background:linear-gradient(90deg,#34d399,#10b981);"></div>
      <div class="s-icon" style="background:rgba(52,211,153,.14);">✅</div>
      <div class="s-num">{{ number_format($summary['voted'], 0, ',', '.') }}</div>
      <div class="s-lbl">Sudah Memilih</div>
    </div>
    <div class="stat-card" style="animation-delay:.25s">
      <div class="glow-line" style="background:linear-gradient(90deg,#fbbf24,#f97316);"></div>
      <div class="s-icon" style="background:rgba(251,191,36,.14);">⏳</div>
      <div class="s-num">{{ number_format($summary['belum'], 0, ',', '.') }}</div>
      <div class="s-lbl">Belum Memilih</div>
    </div>
    <div class="stat-card" style="animation-delay:.35s">
      <div class="glow-line" style="background:linear-gradient(90deg,#f43f5e,#fb7185);"></div>
      <div class="s-icon" style="background:rgba(244,63,94,.14);">♻️</div>
      <div class="s-num" style="font-size:1.15rem; line-height:1.5;">Reset Voting</div>
      <button class="btn btn-danger btn-sm mt-1" data-open-modal="modalReset">Hapus Semua Suara</button>
    </div>
  </div>

  <!-- ============ UPLOAD EXCEL ============ -->
  <div class="panel mb-3">
    <div class="panel-head">
      <div>
        <h3>📥 Upload Database DPT dari Excel</h3>
        <div class="sub">Format kolom: <b>NIK</b> (16 digit) &amp; <b>NAMA</b> — file .xlsx / .xls / .csv, maks 5 MB</div>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.dpt.import') }}" enctype="multipart/form-data" id="importForm">
      @csrf
      <div class="dropzone" id="dropzone">
        <span class="dz-icon">📊</span>
        <div class="dz-title">Tarik &amp; letakkan file Excel di sini, atau klik untuk memilih</div>
        <div class="dz-sub">Data warga yang sudah memilih tidak akan diubah (terkunci)</div>
      </div>
      <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" hidden required>
      <div id="fileName" class="mt-2"></div>
      <button type="submit" class="btn btn-primary mt-2">🚀 Proses Import DPT</button>
    </form>
  </div>

  <!-- ============ DAFTAR WARGA ============ -->
  <div class="table-wrap">
    <div style="padding:1.3rem 1.4rem .8rem;">
      <form method="GET" action="{{ route('admin.dpt.index') }}" class="flex-between">
        <input type="text" name="q" class="form-control" style="max-width:330px;"
               placeholder="🔍 Cari NIK atau nama warga..." value="{{ $search }}">
        <div class="flex-between" style="gap:.4rem;">
          <select name="filter" class="form-control" style="width:auto;" onchange="this.form.submit()">
            <option value="semua"  {{ $filter === 'semua' ? 'selected' : '' }}>Semua Status</option>
            <option value="sudah"  {{ $filter === 'sudah' ? 'selected' : '' }}>Sudah Memilih</option>
            <option value="belum"  {{ $filter === 'belum' ? 'selected' : '' }}>Belum Memilih</option>
          </select>
          <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        </div>
      </form>
    </div>

    <div class="table-scroll">
      <table class="tbl">
        <thead>
          <tr><th>#</th><th>NIK KTP</th><th>Nama Warga</th><th>Status</th><th>Pilihan</th><th>Waktu Memilih</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          @forelse($voters as $v)
          <tr>
            <td class="muted">{{ $voters->firstItem() + $loop->index }}</td>
            <td style="font-variant-numeric:tabular-nums; letter-spacing:.06em;">{{ $v->nik }}</td>
            <td><b>{{ $v->name }}</b></td>
            <td>
              @if($v->is_voted)
                <span class="badge badge-success">✔ SUDAH</span>
              @else
                <span class="badge badge-muted">BELUM</span>
              @endif
            </td>
            <td>{{ $v->candidate ? 'No. '.str_pad($v->candidate->number, 2, '0', STR_PAD_LEFT).' — '.$v->candidate->name : '—' }}</td>
            <td>{{ $v->voted_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
            <td>
              @unless($v->is_voted)
                <form method="POST" action="{{ route('admin.dpt.destroy', $v) }}"
                      onsubmit="return confirm('Hapus {{ $v->name }} dari DPT?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">🗑️</button>
                </form>
              @endunless
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center muted" style="padding:2.4rem;">
            Belum ada data DPT. Upload file Excel di atas untuk menambahkan warga.
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($voters->hasPages())
    <div class="pagination">
      {{ $voters->links('admin.pagination')->render() }}
    </div>
    @endif
  </div>

  <!-- ============ MODAL RESET ============ -->
  <div class="modal-overlay" id="modalReset">
    <div class="modal-box">
      <h3 class="modal-title">⚠️ Reset Seluruh Data Voting</h3>
      <div class="alert alert-danger" style="line-height:1.8;">
        Tindakan ini akan <b>menghapus SEMUA suara masuk</b> dan mengembalikan status seluruh
        warga DPT menjadi "belum memilih". Data DPT tetap tersimpan.
      </div>
      <form method="POST" action="{{ route('admin.voting.reset') }}">
        @csrf
        <label class="form-label">Konfirmasi Password Admin</label>
        <input type="password" name="password" class="form-control" placeholder="Password Anda" required>
        <div class="modal-actions">
          <button type="submit" class="btn btn-danger">♻️ Ya, Reset Semua Suara</button>
          <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        </div>
      </form>
    </div>
  </div>

@endsection
