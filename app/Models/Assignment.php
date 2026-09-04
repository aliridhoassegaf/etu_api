<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Assignment extends Model
{
    protected $table = 'assignment';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'company_vehicle_rental_period_id',
        'start_date',
        'end_date',
        'status'
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

    public function getStartDateAttribute()
    {
        return Carbon::parse($this->attributes['start_date'], 'Asia/Jakarta')
            ->translatedFormat('d F Y');
    }

    public function getEndDateAttribute()
    {
        return Carbon::parse($this->attributes['end_date'], 'Asia/Jakarta')
            ->translatedFormat('d F Y');
    }

}