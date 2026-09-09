<?php

namespace App\Domain\Stations\Models;

use App\Models\StationRoute;
use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;

    protected $primaryKey = 'station_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'station_id',
        'name',
        'short_name',
        'lat',
        'lon',
        'address',
        'post_code',
        'rental_methods',
        'capacity',
    ];

    /**
     * @return HasMany<StationRoute, $this>
     */
    public function routesFrom(): HasMany
    {
        return $this->hasMany(StationRoute::class, 'origin_station_id', 'station_id');
    }

    /**
     * @return HasMany<StationRoute, $this>
     */
    public function routesTo(): HasMany
    {
        return $this->hasMany(StationRoute::class, 'destination_station_id', 'station_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lon' => 'float',
            'rental_methods' => 'array',
            'capacity' => 'integer',
        ];
    }
}
