<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VehicleCatalog extends Model
{
    protected $table = 'vehicle_catalog';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'vehicle_model_id',
        'description',
        'status',
        'sort',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

        });

    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'], 'Asia/Jakarta')
            ->translatedFormat('d F Y H:i:s');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'], 'Asia/Jakarta')
            ->translatedFormat('d F Y H:i:s');
    }

    public function getStatusNameAttribute()
    {
        return match ((int) $this->status) {
            1 => 'Active',
            2 => 'Not Active',
            default => 'Unknown',
        };
    }

    protected $appends = [
        'status_name',
    ];
}