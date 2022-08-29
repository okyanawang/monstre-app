<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updatePersonality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personality' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $user->personality = $request->personality;
        $user->save();
        return response()->json($user);
    }

    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($image = $request->file('avatar')) {
            $user = Auth::user();
            $destinationPath = public_path('/storage/images/avatars/' . $user->id);
            $name = $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $name);

            $user->avatar = $name;
            $user->save();
            return response()->json($user);
        }
    }
}
