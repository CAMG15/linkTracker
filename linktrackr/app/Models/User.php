<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        //'password' => 'hashed',
    ];

    /**
     * Get all links for this user
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get total clicks across all user's links
     */
    public function getTotalClicksAttribute(): int
    {
        return Click::whereIn('link_id', $this->links->pluck('id'))->count();
    }

    /**
     * Get user's most clicked link
     */
    public function getMostClickedLink()
    {
        return $this->links()
            ->withCount('clicks')
            ->orderBy('clicks_count', 'desc')
            ->first();
    }
}