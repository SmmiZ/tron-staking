<?php

namespace App\Models;

use App\Services\TronApi\Tron;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
        'sort', //todo удалить, если не будет использоваться
        'invitation_code',
        'the_code',
        'linear_path',
        'leader_level',
        'photo',
    ];

    public function photo(): Attribute
    {
        return Attribute::get(fn($q) => Storage::disk('public')->url($this->attributes['photo'] ?? 'default.png'));
    }

    /** Исключаем системного юзера из всех запросов по умолчанию */
    public function newQuery(): Builder
    {
        return parent::newQuery()->whereNot('id', 1);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function internalTxs(): HasMany
    {
        return $this->hasMany(InternalTx::class);
    }

    public function stakes(): HasMany
    {
        return $this->hasMany(Stake::class);
    }

    public function leader(): HasOne
    {
        return $this->hasOne(User::class, 'the_code', 'invitation_code');
    }

    public function reactors(): HasMany
    {
        return $this->hasMany(Reactor::class);
    }

    /**
     * Аккаунты-потребители энергии, которые привязаны к пользователю
     *
     * @return HasMany
     */
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

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(OrderExecutor::class);
    }

    /**
     * Запрос на получение из БД транзакций сети TRON, в которых участвовали кошельки юзера
     *
     * @return Builder
     */
    public function scopeTronTxs(): Builder
    {
        $tron = new Tron();
        $wallets = $this->wallets()->pluck('address')->map(fn($address) => $tron->address2HexString($address))->toArray();

        return TronTx::query()->whereIn('from', $wallets)->orWhereIn('to', $wallets);
    }
}
