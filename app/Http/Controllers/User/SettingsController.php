<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Rules\CheckSamePassword;
use App\Rules\MatchOldPassword;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    //
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // TODO:note - number para la versión < 7 de Laravel
        // 'location.latitude'  => ['required', 'number', 'min:-90', 'max:90'],
        // 'location.longitude' => ['required', 'number', 'min:-180', 'max:180']

        $this->validate($request, [
            'tagline'            => ['required'],
            'name'               => ['required'],
            'about'              => ['required', 'string', 'min:20'],
            'formatted_address'  => ['required'],
            'location.latitude'  => ['required', 'numeric', 'min:-90', 'max:90'],
            'location.longitude' => ['required', 'numeric', 'min:-180', 'max:180']
        ]);

        $location = new Point($request->location['latitude'], $request->location['longitude']);

        $user->update([
            'name'              => $request->name,
            'formatted_address' => $request->fotmatted_address,
            'location'          => $location,
            'available_to_hire' => $request->available_for_hire,
            'about'             => $request->about,
            'tagline'           => $request->tagline,
        ]);

        return new UserResource($user);
    }

    public function updatePassword(Request $request)
    {
        // Current password
        // New Password
        // Password Confirmation
        $this->validate($request, [
            'current_password' => ['required', new MatchOldPassword],
            'password'        => ['required', 'confirmed', 'min:6', new CheckSamePassword]
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password)
        ]);

        return response()->json(['message' => 'Password updated'], 200);
    }    
}
