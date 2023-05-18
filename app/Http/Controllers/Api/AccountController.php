<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserUpdateRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AccountController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($currentPhoto = $request->user()->getRawOriginal('photo')) {
                Storage::disk('public')->delete($currentPhoto);
            }

            $image = Image::make($request->photo->getRealPath())->encode('jpg');
            $imageName = Str::uuid() . '.jpg';

            Storage::disk('public')->put($imageName, $image->__toString());
            $data['photo'] = $imageName;
        }

        $request->user()->update($data);

        return new UserResource($request->user());
    }
}
