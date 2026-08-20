<?php

namespace Modules\Developers\Models;

use App\Support\Bilingual;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use Bilingual;

    protected $fillable = ['name', 'name_en', 'about', 'about_en', 'logo', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];
}
