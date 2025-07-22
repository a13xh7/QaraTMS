<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MonthlyContribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'year',
        'month',
        'month_name',
        'username',
        'name',
        'squad',
        'mr_created',
        'mr_approved',
        'repo_pushes',
        'total_events',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'mr_created' => 'integer',
        'mr_approved' => 'integer',
        'repo_pushes' => 'integer',
        'total_events' => 'integer',
    ];

    /**
     * Get contributions for a specific year and month
     *
     * @param int $year
     * @param int|string $month 'all' for all months or specific month number
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getContributions($year, $month = 'all')
    {
        $query = self::where('year', $year);
        
        if ($month !== 'all') {
            $query->where('month', $month);
        }
        
        return $query->orderBy('month')->orderBy('name')->get();
    }

    /**
     * Get all unique years that have contribution data
     *
     * @return array
     */
    public static function getUniqueYears()
    {
        return self::select('year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
    }
}
