<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Vehicle extends Model
{
    protected $table = 'vehicle';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

            if (!$model->slug && $model->name) {
                $slug = Str::slug($model->name);
                $model->slug = $slug;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->name);
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

}