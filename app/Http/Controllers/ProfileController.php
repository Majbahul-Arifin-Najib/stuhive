<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private FileUploader $uploader) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load(['student', 'faculty']),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->uploader->replace($request->file('photo'), 'avatars', $user->photo_path);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update(Arr::only($data, ['name', 'email', 'password', 'photo_path']));

        $user->student?->update(Arr::only($data, ['department']));
        $user->faculty?->update(array_filter(Arr::only($data, ['designation', 'desk_no']), fn ($value) => $value !== null));

        return back()->with('status', 'Profile updated.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->uploader->delete($user->photo_path);
        $user->update(['photo_path' => null]);

        return back()->with('status', 'Profile photo removed.');
    }
}
