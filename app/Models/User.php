<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough, HasOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'sort',
        'invitation_code',
        'the_code',
        'linear_path',
        'leader_level',
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function tronTxs(): HasManyThrough
    {
        return $this->hasManyThrough(TronTx::class, Wallet::class);
    }

    public function internalTxs(): HasMany
    {
        return $this->hasMany(InternalTx::class);
    }

    public function stake(): HasOne
    {
        return $this->hasOne(Stake::class);
    }

    public function leader(): HasOne
    {
        return $this->hasOne(User::class, 'the_code', 'invitation_code');
    }

    public function reactors(): HasMany
    {
        return $this->hasMany(Reactor::class);
    }

    public function consumers(): HasMany
    {
        return $this->hasMany(Consumer::class);
    }

    public function downgrade(): hasOne
    {
        return $this->hasOne(UserDowngrade::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(UserLine::class);
    }

    public function level(): HasOne
    {
        return $this->hasOne(LeaderLevel::class, 'level', 'leader_level');
    }
}
