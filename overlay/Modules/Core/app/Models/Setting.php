<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['group', 'key', 'value', 'is_public'];

    protected $casts = [
        'value'     => 'json',
        'is_public' => 'boolean',
    ];
}
