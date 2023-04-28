<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\{AuthRequest, CodeRequest, InvitationCodeRequest};
use App\Mail\AuthCode;
use App\Models\{TempCode, User, UserLine};
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
            'data' => [
                'message' => __('messages.code.sent')
            ],
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
            'data' => [
                'signup' => User::where('email', $request->email)->doesntExist()
            ],
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
        $leader = User::withCount('reactors')->firstWhere('the_code', $request->get('invitation_code'));
        $leaderCode = $leader?->reactors_count > 0 ? $request->get('invitation_code') : null;

        $user = User::firstOrCreate(['email' => $request->email], [
            'name' => 'Пользователь ' . random_int(111111, 999999),
            'the_code' => 'TE' . Str::random(6),
            'invitation_code' => $leaderCode,
        ]);

        if ($user->wasRecentlyCreated && $leaderCode) {
            $this->updateLinearPath($user);
        }

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
            'data' => [
                'message' => __('messages.device.logout')
            ]
        ])->withCookie(cookie()->forget('jwt'));
    }

    /**
     * Обновление реферальной цепочки пользователей
     *
     * @param User $user
     * @return void
     */
    private function updateLinearPath(User $user): void
    {
        $leader = $user->leader;
        $linearPath = $leader->linear_path ?? '/' . $leader->id . '/';

        $leadersIds = explode('/', trim($linearPath, '/'));
        foreach ($leadersIds as $i => $leaderId) {
            if ($i > 19) {
                break;
            }

            $lineIds = UserLine::where('user_id', $leaderId)->where('line', $i + 1)->value('ids') ?? [];
            UserLine::updateOrCreate(
                ['user_id' => $leaderId, 'line' => $i + 1],
                ['ids' => array_merge($lineIds, [$user->id])]
            );
        }

        $user->update(['linear_path' => '/' . $user->id . $linearPath]);
    }

    /**
     * Проверка валидности пригласительного кода
     *
     * @param InvitationCodeRequest $request
     * @return Response
     */
    public function checkLeaderCode(InvitationCodeRequest $request): Response
    {
        $leader = User::withCount('reactors')->firstWhere('the_code', $request->invitation_code);

        return response([
            'status' => $leader->reactors_count > 0,
            'data' => [],
        ]);
    }

    /**
     * Обновить лидера в течение 15 минут после регистрации
     *
     * @param InvitationCodeRequest $request
     * @return Response
     */
    public function addLeaderCode(InvitationCodeRequest $request): Response
    {
        $user = $request->user();
        $leader = User::withCount('reactors')->firstWhere('the_code', $request->invitation_code);

        switch (true) {
            case $leader->reactors_count < 0:
                return response([
                    'status' => false,
                    'data' => [
                        'message' => __('messages.code.invalid')
                    ],
                ]);
            case $user->created_at < now()->subMinutes(15):
                return response([
                    'status' => false,
                    'data' => [
                        'message' => __('messages.code.expired')
                    ],
                ]);
            default:
                $user->update(['invitation_code' => $request->invitation_code]);
                $this->updateLinearPath($user);

                return response([
                    'status' => true,
                    'data' => [],
                ]);
        }
    }
}
