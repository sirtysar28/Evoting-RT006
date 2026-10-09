<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Voter;
use App\Services\VoteTrendService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    /** Halaman utama e-voting */
    public function index()
    {
        $candidates = Candidate::orderBy('number')->get();

        $stats = [
            'dpt'      => Voter::count(),
            'voted'    => Voter::where('is_voted', true)->count(),
        ];

        return view('voting.index', compact('candidates', 'stats'));
    }

    /** AJAX: verifikasi NIK ke database DPT */
    public function verifyNik(Request $request)
    {
        $request->validate(['nik' => ['required', 'digits:16']]);

        $voter = Voter::where('nik', $request->nik)->first();

        if (! $voter) {
            return response()->json([
                'status'  => 'error',
                'title'   => 'NIK Tidak Terdaftar',
                'message' => "NIK {$request->nik} tidak ditemukan dalam DPT Warga RT 006. Silakan hubungi Panitia.",
            ]);
        }

        if ($voter->is_voted) {
            return response()->json([
                'status'  => 'error',
                'title'   => 'Sudah Memilih',
                'message' => 'NIK ini sudah menggunakan hak pilih pada ' .
                    $voter->voted_at->translatedFormat('d F Y, H:i') . ' WIB. Dilarang memilih ganda!',
            ]);
        }

        // Simpan sesi pemilih terverifikasi
        session(['verified_voter' => [
            'id'   => $voter->id,
            'nik'  => $voter->nik,
            'name' => $voter->name,
            'key'  => Str::uuid()->toString(), // token anti-replay
        ]]);

        return response()->json([
            'status'  => 'success',
            'title'   => 'Identitas Terverifikasi',
            'voter'   => ['name' => $voter->name, 'nik' => $voter->nik],
            'token'   => session('verified_voter.key'),
        ]);
    }

    /** AJAX: simpan suara */
    public function castVote(Request $request)
    {
        $request->validate([
            'candidate_id' => ['required', 'integer', Rule::exists('candidates', 'id')],
        ]);

        $session = session('verified_voter');
        if (! $session) {
            return response()->json([
                'status'  => 'session_expired',
                'title'   => 'Sesi Berakhir',
                'message' => 'Silakan verifikasi ulang NIK Anda.',
            ], 419);
        }

        $voter = Voter::lockForUpdate()->findOrFail($session['id']);

        if ($voter->is_voted) {
            return response()->json([
                'status'  => 'error',
                'title'   => 'Sudah Memilih',
                'message' => 'NIK ini sudah tercatat memilih. Dilarang memilih ganda!',
            ]);
        }

        $candidate = Candidate::findOrFail($request->candidate_id);
        $ticket    = 'VOTE-RT006-' . strtoupper(Str::random(8));

        \Illuminate\Support\Facades\DB::transaction(function () use ($voter, $candidate, $ticket) {
            $voter->votes()->create([
                'candidate_id' => $candidate->id,
                'ticket_code'  => $ticket,
            ]);

            $voter->update([
                'is_voted'     => true,
                'candidate_id' => $candidate->id,
                'ticket_code'  => $ticket,
                'voted_at'     => now(),
            ]);
        });

        session()->forget('verified_voter');

        return response()->json([
            'status'    => 'success',
            'title'     => 'Suara Berhasil Dicatat',
            'receipt'   => [
                'name'      => $voter->name,
                'nik_mask'  => substr($voter->nik, 0, 6) . '******' . substr($voter->nik, 12, 4),
                'candidate' => $candidate->name,
                'time'      => now()->translatedFormat('d F Y, H:i') . ' WIB',
                'ticket'    => $ticket,
            ],
        ]);
    }

    /** Batalkan sesi verifikasi (kembali ke awal) */
    public function cancelVerification()
    {
        session()->forget('verified_voter');

        return response()->json(['status' => 'success']);
    }

    /* ============================================================
       LEADERBOARD PUBLIK — hasil real-time per calon
       ============================================================ */

    /** Halaman leaderboard */
    public function leaderboard()
    {
        $stats         = $this->leaderboardStats();
        $stats['json'] = json_encode($stats);

        return view('voting.leaderboard', $stats);
    }

    /** AJAX: data live leaderboard (auto-refresh) */
    public function leaderboardLive()
    {
        return response()->json($this->leaderboardStats());
    }

    private function leaderboardStats(): array
    {
        $candidates = Candidate::withCount('voters')->get()
            ->map(fn ($c) => [
                'id'      => $c->id,
                'number'  => (int) $c->number,
                'name'    => $c->name,
                'vision'  => $c->vision,
                'photo'   => $c->photo ? asset('storage/' . $c->photo) : null,
                'color'   => $c->color,
                'votes'   => (int) $c->voters_count,
            ])
            ->sortByDesc('votes')
            ->values()
            ->map(function ($c, $i) {
                $c['rank'] = $i + 1;

                return $c;
            });

        $totalVoted = Voter::where('is_voted', true)->count();
        $totalDpt   = Voter::count();

        $candidates->transform(function ($c) use ($totalVoted) {
            $c['percent'] = $totalVoted > 0 ? round($c['votes'] / $totalVoted * 100, 1) : 0.0;

            return $c;
        });

        // Tren suara per jam (24 jam terakhir) — kompatibel MySQL/SQLite/PostgreSQL
        $trendData = VoteTrendService::hourly();

        return [
            'candidates'   => $candidates->values(),
            'totalVoted'   => $totalVoted,
            'totalDpt'     => $totalDpt,
            'notVoted'     => $totalDpt - $totalVoted,
            'participation'=> $totalDpt > 0 ? round($totalVoted / $totalDpt * 100, 1) : 0,
            'trendData'    => $trendData,
            'updatedAt'    => now()->translatedFormat('H:i:s'),
        ];
    }
}
