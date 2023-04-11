<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'staff';

    protected array $protected = [
        'password',
        'pin',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'access_level',
        'is_enable',
    ];

    public const OPERATOR = 50;
    public const ADMIN = 100;
    public const SUPER_ADMIN = 200;

    public const ROLES = [
        self::OPERATOR => 'Оператор',
        self::ADMIN => 'Администратор',
        self::SUPER_ADMIN => 'Супер администратор',
    ];

    /**
     * Возвращает имя роли
     * Для вызова использовать $staff->role
     *
     * @return string
     */
    public function getRoleAttribute(): string
    {
        return match (true) {
            isset(self::ROLES[$this->access_level]) => self::ROLES[$this->access_level],
            $this->access_level >= self::SUPER_ADMIN => self::ROLES[self::SUPER_ADMIN],
            default => 'Неизвестно'
        };
    }

    /**
     * Возвращает состояние активности
     * Для вызова использовать $staff->activity
     *
     * @return string
     */
    public function getActivityAttribute(): string
    {
        return $this->is_enable ? 'Активен' : 'Заблокирован';
    }

    /**
     * Возвращает css класс для состояние активности
     * Для вызова использовать $staff->activity_class
     *
     * @return string
     */
    public function getActivityClassAttribute(): string
    {
        return $this->is_enable ? 'label-success' : 'label-error';
    }

    /**
     * Проверяет, является ли пользователь оператором
     *
     * @return bool
     */
    public function isOperator(): bool
    {
        return $this->access_level == self::OPERATOR;
    }

    /**
     * Проверяет, является ли пользователь администратором
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->access_level == 100;
    }

    /**
     * Проверяет, является ли пользователь супер-администратором
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->access_level >= 200;
    }

    /**
     * Ограничивает выборку только операторами
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOperators(Builder $query): Builder
    {
        return $query->where('access_level', self::OPERATOR);
    }

    /**
     * Ограничивает выборку только администраторами
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('access_level', self::ADMIN);
    }

    /**
     * Ограничивает выборку только супер-администраторами
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSuperAdmins(Builder $query): Builder
    {
        return $query->where('access_level', '>=', self::SUPER_ADMIN);
    }
}
