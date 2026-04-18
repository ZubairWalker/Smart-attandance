<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrToken extends Model
{
    protected $fillable = [
        'token',
        'office_id',
        'date',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
