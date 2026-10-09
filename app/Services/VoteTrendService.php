<?php

namespace App\Services;

use App\Models\Voter;
use Illuminate\Support\Facades\DB;

/**
 * Query tren suara per jam yang kompatibel lintas database
 * (MySQL/MariaDB, PostgreSQL, SQLite).
 */
class VoteTrendService
{
    /**
     * Jumlah suara masuk per jam dalam 24 jam terakhir.
     * Return: ['00' => int, '01' => int, ..., '23' => int]
     */
    public static function hourly(int $hours = 24): array
    {
        $expression = self::hourExpression();

        $trend = Voter::where('is_voted', true)
            ->where('voted_at', '>=', now()->subHours($hours))
            ->selectRaw("{$expression} as jam, count(*) as total")
            ->groupBy('jam')
            ->pluck('total', 'jam');

        $result = [];
        for ($i = 0; $i <= 23; $i++) {
            $jam         = str_pad($i, 2, '0', STR_PAD_LEFT);
            $result[$jam] = (int) ($trend[$jam] ?? 0);
        }

        return $result;
    }

    /**
     * Ekspresi SQL untuk mengambil jam (2 digit) dari kolom voted_at
     * sesuai driver database aktif.
     */
    private static function hourExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT(voted_at, '%H')",
            'pgsql'            => "TO_CHAR(voted_at, 'HH24')",
            default            => "strftime('%H', voted_at)", // sqlite & lainnya
        };
    }
}
