<?php

namespace App\Domain\Stations\Http\Resources;

use App\Domain\Stations\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Station
 */
class StationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'station_id' => $this->station_id,
            'name' => $this->name,
            'lat' => $this->lat,
            'lon' => $this->lon,
        ];
    }
}
