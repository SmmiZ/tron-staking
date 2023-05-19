<?php

namespace App\View\Components;

use App\Models\{Consumer, InternalTx, Order, ResourceConsumption, TronTx, User, Withdrawal};
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Menu extends Component
{
    public $menu = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->addMenu('home', '', 'Панель управления', 'mdi-monitor-dashboard');

        $this->addMenu('users.index', 'users', 'Клиенты', 'mdi-account-badge-horizontal-outline', User::class);

        $this->addMenu('consumers.index', 'consumers', 'Потребители', 'mdi-account-arrow-left', Consumer::class);

        $this->addMenu('orders.index', 'orders', 'Заказы', 'mdi-application', Order::class);

        $this->addMenu('transactions.tron.index', 'tron', 'Внеш. транзакции', 'mdi-arrow-left-right-bold-outline', TronTx::class);

        $this->addMenu('transactions.internal.index', 'internal', 'Внутр. транзакции', 'mdi-home-circle', InternalTx::class);

        $this->addMenu('resource-consumption', 'resource-consumption', 'Статистика ресурсов', 'mdi-select-compare', ResourceConsumption::class);

        $this->addMenu('withdrawals.index', 'withdrawals', 'Заявки на вывод', 'mdi-bank-transfer-out', Withdrawal::class);
    }

    /**
     * Добавляет элемент меню
     *
     * @param string $routeName
     * @param string $segment
     * @param string $linkName
     * @param string $icon
     * @param string|null $className
     * @return void
     */
    protected function addMenu(string $routeName, string $segment, string $linkName, string $icon, ?string $className = null): void
    {
        $canView = !$className || auth()->user()->can('viewAny', $className);

        if ($canView) {
            $isActive = in_array($segment, request()->segments()) || request()->routeIs($routeName);

            $this->menu[] = [
                'href' => route($routeName),
                'active' => $isActive ? 'active' : '',
                'icon' => $icon,
                'text' => $linkName,
            ];
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.menu');
    }
}
