<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Admin extends Authenticatable implements JWTSubject
{
    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'admin_role_id',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

            if (!$model->slug && $model->fullname) {
                $slug = Str::slug($model->fullname);

                $count = self::where('slug', 'like', $slug . '%')->count();
                $model->slug = $count ? $slug . '-' . ($count + 1) : $slug;
            }

        });

        static::updating(function ($model) {
            if ($model->isDirty('fullname')) {
                $model->slug = Str::slug($model->fullname);
            }
        });
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'], 'Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y H:i:s');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'], 'Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y H:i:s');
    }

    protected $hidden = [
        'password'
    ];

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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}