<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'remarks',
        'balance_before',
        'balance_after',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            $issue->uuid = Str::uuid(36);
        });
    }
}
