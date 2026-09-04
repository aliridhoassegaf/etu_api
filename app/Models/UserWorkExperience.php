<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserWorkExperience extends Model
{
    protected $table = 'user_work_experience';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'sort',
        'status',
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