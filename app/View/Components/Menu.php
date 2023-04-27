<?php

namespace App\View\Components;

use App\Models\{Consumer, Order, TronTx, User};
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\{Auth};
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

        $this->addMenu('users.index', 'users', 'Клиенты', 'mdi-human', User::class);

        $this->addMenu('consumers.index', 'consumers', 'Потребители', 'mdi-account-arrow-left', Consumer::class);

        $this->addMenu('orders.index', 'orders', 'Заказы', 'mdi-application', Order::class);

        $this->addMenu('transactions.index', 'transactions', 'Транзакции', 'mdi-arrow-left-right-bold-outline', TronTx::class);
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
    protected function addMenu(string $routeName, string $segment, string $linkName, string $icon, ?string $className = NULL): void
    {
        $canView = !$className || Auth::user()->can('viewAny', $className);

        if ($canView) {
            $this->menu[] = [
                'href' => route($routeName),
                'active' => request()->segment(2) == $segment ? 'active' : '',
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
