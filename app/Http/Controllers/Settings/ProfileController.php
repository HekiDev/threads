<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = User::withCount('followers')->find(auth()->id());

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'tab' => 'threads',
            'user' => new UserProfileResource($user),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function getReplies(Request $request)
    {
        $user = User::withCount('followers')->find(auth()->id());

        return Inertia::render('Profile/Replies', [
            'tab' => 'replies',
            'user' => new UserProfileResource($user),
        ]);
    }

    public function getMedia(Request $request)
    {
        $user = User::withCount('followers')->find(auth()->id());

        return Inertia::render('Profile/Media', [
            'tab' => 'media',
            'user' => new UserProfileResource($user),
        ]);
    }

    public function getShares(Request $request)
    {
        $user = User::withCount('followers')->find(auth()->id());

        return Inertia::render('Profile/Shares', [
            'tab' => 'shares',
            'user' => new UserProfileResource($user),
        ]);
    }
}
