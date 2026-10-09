<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DptController extends Controller
{
    /** Halaman kelola DPT warga */
    public function index(Request $request)
    {
        $search = $request->get('q');
        $filter = $request->get('filter'); // semua | sudah | belum

        $voters = Voter::with('candidate')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('nik', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->when($filter === 'sudah', fn ($q) => $q->where('is_voted', true))
            ->when($filter === 'belum', fn ($q) => $q->where('is_voted', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Voter::count(),
            'voted' => Voter::where('is_voted', true)->count(),
            'belum' => Voter::where('is_voted', false)->count(),
        ];

        return view('admin.dpt', compact('voters', 'summary', 'search', 'filter'));
    }

    /** Upload & import file Excel DPT (kolom: NIK, Nama) */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.mimes' => 'Format harus .xlsx, .xls, atau .csv',
        ]);

        try {
            $rows = IOFactory::load($request->file('file')->getPathname())
                ->getActiveSheet()
                ->toArray(null, true, true, true);

            [$nikCol, $namaCol, $startRow] = $this->detectColumns($rows);

            $inserted = 0;
            $updated  = 0;
            $skipped  = 0;

            DB::transaction(function () use ($rows, $nikCol, $namaCol, $startRow, &$inserted, &$updated, &$skipped) {
                for ($i = $startRow; $i <= count($rows); $i++) {
                    $nik  = preg_replace('/\D/', '', (string) ($rows[$i][$nikCol] ?? ''));
                    $nama = trim((string) ($rows[$i][$namaCol] ?? ''));

                    if (strlen($nik) !== 16 || $nama === '') {
                        if ($nik !== '' || $nama !== '') {
                            $skipped++;
                        }
                        continue;
                    }

                    $existing = Voter::where('nik', $nik)->first();
                    if ($existing) {
                        if (! $existing->is_voted) {
                            $existing->update(['name' => $nama]);
                            $updated++;
                        } else {
                            $skipped++; // sudah memilih, data dikunci
                        }
                    } else {
                        Voter::create(['nik' => $nik, 'name' => $nama]);
                        $inserted++;
                    }
                }
            });

            return back()->with('success',
                "Import DPT selesai! Baru: {$inserted} warga, Diperbarui: {$updated}, Dilewati: {$skipped}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: pastikan ada kolom "NIK" dan "Nama".');
        }
    }

    /**
     * Deteksi kolom NIK & Nama dari baris header.
     * Return: [kolomNik, kolomNama, barisMulaiData]
     */
    private function detectColumns(array $rows): array
    {
        foreach ($rows as $rowIdx => $row) {
            $nikCol = null;
            $namaCol = null;

            foreach ($row as $colIdx => $value) {
                $clean = strtolower(trim((string) $value));
                if ($clean === '') {
                    continue;
                }
                if ($nikCol === null && str_contains($clean, 'nik')) {
                    $nikCol = $colIdx;
                }
                if ($namaCol === null && str_contains($clean, 'nama')) {
                    $namaCol = $colIdx;
                }
            }

            if ($nikCol !== null && $namaCol !== null) {
                return [$nikCol, $namaCol, $rowIdx + 1];
            }

            // Baris tanpa header tapi berisi 16 digit angka -> anggap data (kolom A=NIK, B=Nama)
            $first = preg_replace('/\D/', '', (string) reset($row));
            if (strlen($first) === 16) {
                $keys = array_keys($row);

                return [$keys[0], $keys[1] ?? $keys[0], $rowIdx];
            }
        }

        return ['A', 'B', 2]; // fallback default
    }

    /** Unduh template Excel DPT */
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NIK', 'NAMA'],
            ['3172040101800001', 'Contoh Nama Warga Satu'],
            ['3172040101800002', 'Contoh Nama Warga Dua'],
        ], null, 'A1');
        foreach (['A', 'B'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $file   = storage_path('app/template-dpt-rt006.xlsx');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($file);

        return response()->download($file, 'Template-DPT-RT006.xlsx')->deleteFileAfterSend();
    }

    /** Export DPT + hasil voting ke Excel */
    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray([['NIK', 'NAMA', 'STATUS', 'WAKTU_MEMILIH', 'KANDIDAT', 'TIKET']], null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $row = 2;
        foreach (Voter::with('candidate')->orderBy('name')->cursor() as $v) {
            $sheet->fromArray([[
                $v->nik,
                $v->name,
                $v->is_voted ? 'SUDAH' : 'BELUM',
                $v->voted_at?->format('Y-m-d H:i:s') ?? '-',
                $v->candidate?->name ?? '-',
                $v->ticket_code ?? '-',
            ]], null, 'A' . $row++);
        }
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $file   = storage_path('app/dpt-rt006-' . date('Ymd-His') . '.xlsx');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($file);

        return response()->download($file, 'DPT-RT006-' . date('Ymd-His') . '.xlsx')->deleteFileAfterSend();
    }

    /** Reset seluruh data voting (suara dihapus, DPT tetap) */
    public function resetVoting(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        $admin = Admin::find(session('admin_id'));
        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return back()->with('error', 'Password admin salah! Reset dibatalkan.');
        }

        DB::transaction(function () {
            Vote::query()->delete();
            Voter::query()->update([
                'is_voted'     => false,
                'candidate_id' => null,
                'ticket_code'  => null,
                'voted_at'     => null,
            ]);
        });

        return back()->with('success', 'Seluruh data suara berhasil di-reset. DPT warga tetap terdaftar.');
    }

    /** Hapus satu warga dari DPT */
    public function destroy(Voter $voter)
    {
        $voter->delete();

        return back()->with('success', "Warga {$voter->name} dihapus dari DPT.");
    }

    /** Halaman kelola calon ketua RT */
    public function candidates()
    {
        $candidates = Candidate::withCount('voters')->orderBy('number')->get();

        return view('admin.candidates', compact('candidates'));
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'vision' => ['nullable', 'string', 'max:500'],
            'photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'color'  => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $data = $request->only('name', 'vision', 'color');
        if (empty($data['color'])) {
            unset($data['color']);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('calon', 'public');
        }

        $candidate->update($data);

        return back()->with('success', 'Data calon no. urut ' . $candidate->number . ' diperbarui.');
    }
}
