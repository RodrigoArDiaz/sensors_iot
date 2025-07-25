<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorTemperatureHumidity extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sensor_temperature_humity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'temperature',
        'humidity',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Solo usamos created_at, no updated_at
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'temperature' => 'string',
        'humidity' => 'string',
        'created_at' => 'datetime',
    ];

    /**
     * Deshabilitar updated_at ya que solo tenemos created_at
     */
    const UPDATED_AT = null;
}
