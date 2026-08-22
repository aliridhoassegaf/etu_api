<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminActivity extends Model
{
    protected $table = 'admin_activity';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'target_id',
        'target_name',
        'description',
        'ip',
        'changes',
        'admin_id',
        'created_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

            if (!$model->created_at) {
                $model->created_at = now();
            }

        });
    }

    public function getCreatedAtFormattedAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        return $this->created_at
            ->timezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y H:i:s');
    }
}