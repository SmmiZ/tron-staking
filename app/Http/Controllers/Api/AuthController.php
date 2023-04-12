<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\{AuthRequest, CodeRequest};
use App\Mail\AuthCode;
use App\Models\{TempCode, User};
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Отправить код для аутентификации
     *
     * @param CodeRequest $request
     * @return Response
     */
    public function code(CodeRequest $request): Response
    {
        $tempCode = TempCode::create(['login' => $request->email, 'code' => rand(1000, 9999)]);

        Mail::to($request->email)->send(new AuthCode($tempCode->code));

        return response([
            'status' => true,
            'message' => __('messages.code.sent'),
        ]);
    }

    /**
     * Проверка валидности кода
     *
     * @param AuthRequest $request
     * @return Response
     */
    public function checkCode(AuthRequest $request): Response
    {
        return response([
            'status' => true,
            'message' => __('messages.code.valid'),
        ]);
    }

    /**
     * Регистрация/вход
     *
     * @param AuthRequest $request
     * @return Response
     * @throws Exception
     */
    public function auth(AuthRequest $request): Response
    {
        $user = User::firstOrCreate(['email' => $request->email], ['name' => 'Пользователь ' . random_int(111111, 999999)]);

        $token = $user->createToken(
            $request->get('device_name', Str::uuid()),
            ['user_agent' => request()->header('user-agent')]
        );

        return response([
            'status' => true,
            'data' => [
                'token' => $token->plainTextToken,
                'default_name' => preg_match('/^Пользователь [1-9]+/', $user->name) || !$user->name,
            ],
        ])->withCookie(cookie('jwt', $token->plainTextToken, 60 * 24 * 365));
    }

    /**
     * Выход из аккаунта на текущем устройстве
     *
     * @return Response
     */
    public function logout(): Response
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return response([
            'status' => true,
            'message' => __('messages.device.logout')
        ])->withCookie(cookie()->forget('jwt'));
    }
}