<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, MassPrunable, Model};

class TempCode extends Model
{
    use MassPrunable;

    protected $table = 'temp_codes';

    protected $fillable = [
        'login',
        'code',
    ];

    protected $casts = [
        'code' => 'integer',
    ];

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMinutes(15));
    }

    /**
     * Проверяет есть ли данный код у авторизированного пользователя
     * или конкретного логина
     *
     * @param int $code
     * @param string $email
     * @return bool
     */
    public static function checkCode(int $code, string $email): bool
    {
        $tempCode = self::latest()->where('login', $email)->value('code');

        return $tempCode === $code;
    }
}
