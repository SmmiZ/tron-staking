<div>
    <div class="mb-40">
        @error('email')<div class="auth-invalid" role="alert">{{$message}}</div>@enderror
        @error('code')<div class="auth-invalid" role="alert">{{$message}}</div>@enderror
        @error('password')<div class="auth-invalid" role="alert">{{$message}}</div>@enderror
        @if (session()->has('passcode'))
            <div class="auth-invalid" role="alert">{{session('passcode')}}</div>
        @endif
        <div class="auth-input">
            <label for="email"><span class="mdi mdi-email-check"></span></label>
            <input id="email" type="email" autocomplete="email" autofocus placeholder="E-mail" wire:model="email">
        </div>
        @if($showCode)
            <div class="auth-input">
                <label for="code"></label>
                <input id="code" type="text" autofocus placeholder="Код из почты" wire:model="code">
            </div>
        @else
            <div class="auth-input">
                <div></div>
                <button class="login-send-code" type="button" wire:click="sendCode()">Отправить код</button>
            </div>
        @endif
        <div class="auth-input">
            <label for="password"><span class="mdi mdi-key-variant"></span></label>
            <input id="password" type="password" autocomplete="current-password" placeholder="Пароль"
                   wire:model="password">
        </div>
    </div>
    <div class="auth-bts">
        <button class="auth-btn" wire:click="login()">Войти</button>
    </div>
</div>
