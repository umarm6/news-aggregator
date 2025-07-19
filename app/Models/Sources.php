<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sources extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'api_endpoint',
        'api_key_required',
        'rate_limit',
        'is_active'
    ];

    protected $casts = [
        'api_key_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Articles::class);
    }

}
