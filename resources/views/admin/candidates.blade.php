@extends('admin.layout')

@section('title', 'Data Calon Ketua RT')

@section('content')

  <div class="panel mb-3">
    <div class="panel-head">
      <div>
        <h3>🧑‍💼 Kelola Data Calon Ketua RT 006</h3>
        <div class="sub">Perbarui nama, visi, warna kartu, dan unggah foto paslon (akan tampil di halaman voting)</div>
      </div>
    </div>

    <div class="candidates-grid">
      @foreach($candidates as $c)
      <div class="candidate-card" style="pointer-events:auto;">
        <div class="no-urut">No. {{ str_pad($c->number, 2, '0', STR_PAD_LEFT) }}</div>

        @if($c->photo)
          <div class="foto-frame"><img src="{{ asset('storage/' . $c->photo) }}" alt="{{ $c->name }}"></div>
        @else
          <div class="foto-frame"><div class="foto-placeholder">👤</div></div>
        @endif

        <div class="candidate-name">{{ $c->name }}</div>
        <div class="candidate-vision">"{{ $c->vision }}"</div>
        <div class="small muted mb-2">{{ $c->voters_count }} suara saat ini</div>

        <button type="button" class="btn btn-ghost btn-sm btn-block" data-toggle-edit="edit-{{ $c->id }}">
          ✏️ Edit Data Calon
        </button>

        <form method="POST" action="{{ route('admin.candidates.update', $c) }}"
              enctype="multipart/form-data" id="edit-{{ $c->id }}" style="display:none; margin-top:1rem; text-align:left;">
          @csrf @method('PUT')

          <div class="mb-2">
            <label class="form-label">Nama Calon</label>
            <input type="text" name="name" class="form-control" value="{{ $c->name }}" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Visi / Misi</label>
            <textarea name="vision" class="form-control" rows="3">{{ $c->vision }}</textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Warna Kartu (hex)</label>
            <input type="color" name="color" class="form-control" style="height:48px; padding:.3rem;" value="{{ $c->color }}">
          </div>
          <div class="mb-2">
            <label class="form-label">Foto Calon (jpg/png, maks 2MB)</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
          </div>
          <button type="submit" class="btn btn-primary btn-sm btn-block">💾 Simpan Perubahan</button>
        </form>
      </div>
      @endforeach
    </div>
  </div>

@endsection
