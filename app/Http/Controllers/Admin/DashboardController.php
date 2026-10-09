<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Voter;
use App\Services\VoteTrendService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Dashboard monitoring hasil (grafik & diagram) */
    public function index()
    {
        $stats = $this->statistics();

        // Data untuk Chart.js — di-encode di controller agar aman dari parser Blade
        $stats['chartData'] = json_encode([
            'candidates' => $stats['candidates']->map(fn ($c) => [
                'number'       => $c->number,
                'name'         => $c->name,
                'voters_count' => $c->voters_count,
                'percent'      => $c->percent,
                'color'        => $c->color,
            ])->values(),
            'totalDpt'     => $stats['totalDpt'],
            'totalVoted'   => $stats['totalVoted'],
            'notVoted'     => $stats['notVoted'],
            'participation'=> $stats['participation'],
            'trendData'    => $stats['trendData'],
            'recent'       => $stats['recent']->map(fn ($v) => [
                'name'     => $v->name,
                'nik'      => $v->nik,
                'candidate'=> [
                    'number' => $v->candidate?->number,
                    'name'   => $v->candidate?->name,
                ],
                'voted_at' => $v->voted_at?->translatedFormat('d M Y, H:i'),
            ]),
        ]);

        return view('admin.dashboard', $stats);
    }

    /** AJAX: data live untuk auto-refresh grafik */
    public function live()
    {
        return response()->json($this->statistics());
    }

    private function statistics(): array
    {
        $candidates = Candidate::orderBy('number')
            ->withCount('voters')
            ->get();

        $totalDpt   = Voter::count();
        $totalVoted = Voter::where('is_voted', true)->count();
        $notVoted   = $totalDpt - $totalVoted;

        // Tren suara per jam (24 jam terakhir) — kompatibel MySQL/SQLite/PostgreSQL
        $trendData = VoteTrendService::hourly();

        // 10 pemilih terbaru
        $recent = Voter::with('candidate')
            ->where('is_voted', true)
            ->latest('voted_at')
            ->limit(10)
            ->get();

        // Quick count (perhitungan cepat)
        $quickCount = $candidates->map(function ($c) use ($totalVoted) {
            $c->percent = $totalVoted > 0 ? round($c->voters_count / $totalVoted * 100, 1) : 0;

            return $c;
        });

        $leading = $quickCount->sortByDesc('voters_count')->first();
        $isTie   = $quickCount->count() > 1
            && $quickCount->first()->voters_count === $quickCount->skip(1)->first()->voters_count;

        return [
            'candidates' => $quickCount,
            'totalDpt'   => $totalDpt,
            'totalVoted' => $totalVoted,
            'notVoted'   => $notVoted,
            'participation' => $totalDpt > 0 ? round($totalVoted / $totalDpt * 100, 1) : 0,
            'trendData'  => $trendData,
            'recent'     => $recent,
            'leading'    => $isTie ? null : $leading,
        ];
    }
}
