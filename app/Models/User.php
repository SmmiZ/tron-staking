<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough, HasOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    public function stake(): HasOne
    {
        return $this->hasOne(Stake::class);
    }

    public function leader(): HasOne
    {
        return $this->hasOne(User::class, 'the_code', 'invitation_code');
    }

    public function reactor(): HasOne
    {
        return $this->hasOne(Reactor::class);
    }

    /**
     * Считает кол-во приглашенных в указанной линии
     *
     * @param int $lineNum
     * @return int
     */
    public function getLineCount(int $lineNum): int
    {
        return self::where('linear_path', 'rlike', '^(/\d+){' . $lineNum . "}/$this->id/")->count('id');
    }

    /**
     * Все доступные дочерние клиенты из реферального дерева
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInvitedClients(Builder $query): Builder
    {
        return $query->where('linear_path', 'rlike', "^(/\d+){1,20}/$this->id/");
    }
}
