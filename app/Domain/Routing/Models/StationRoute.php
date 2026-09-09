<?php

namespace App\Domain\Routing\Models;

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Stations\Models\Station;
use Database\Factories\StationRouteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationRoute extends Model
{
    /** @use HasFactory<StationRouteFactory> */
    use HasFactory;

    protected $fillable = [
        'origin_station_id',
        'destination_station_id',
        'mode',
        'distance_meters',
        'duration_seconds',
    ];

    /**
     * @return BelongsTo<Station, $this>
     */
    public function originStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'origin_station_id', 'station_id');
    }

    /**
     * @return BelongsTo<Station, $this>
     */
    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'destination_station_id', 'station_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => TravelMode::class,
            'distance_meters' => 'float',
            'duration_seconds' => 'float',
        ];
    }
}
