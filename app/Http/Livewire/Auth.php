<?php

namespace App\Http\Livewire;

use App\Models\{Staff, TempCode};
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\{Hash};
use Illuminate\Validation\Rule;
use Livewire\Component;

class Auth extends Component
{
    public $email;
    public $password;
    public $code;
    public $showCode = false;

    public function sendCode(): void
    {
        $this->validateCodeRequest();

        $staff = Staff::firstWhere('email', $this->email);
        $code = rand(100000, 999999);

//        TempCode::create(['login' => $this->email, 'code' => $code]);

        //todo код на почту
        $this->showCode = true;
    }

    /**
     * Возвращает Livewire/Redirector|null
     */
    public function login()
    {
//        $this->validateLoginRequest();

        $staff = Staff::where('email', $this->email)->first();
//        $validateCode = TempCode::checkCode($this->code, $staff->email);
        $validatePassword = Hash::check($this->password, $staff->password);

//        if (!$validateCode || !$validatePassword) {
//            session()->flash('passcode', 'Некорректный пароль или код');
//
//            return null;
//        }

        auth('staff')->loginUsingId($staff->id);

        return to_route('home');
    }

    private function validateCodeRequest(): void
    {
        $this->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:64',
                Rule::exists('staff', 'email')->where('is_enable', true),
            ],
        ]);
    }

    private function validateLoginRequest(): void
    {
        $this->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:64',
                Rule::exists('staff', 'email')->where('is_enable', true),
            ],
            'code' => ['required', 'numeric', 'digits:6'],
            'password' => ['required', 'string'],
        ]);
    }

    public function render(): View
    {
        return view('livewire.auth');
    }
}
