<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('react', $post);

        $validated = $request->validate([
            'emoji' => ['required', Rule::in(Reaction::PALETTE)],
        ]);

        $post->reactions()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['emoji' => $validated['emoji']],
        );

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('react', $post);

        $post->reactions()->whereBelongsTo($request->user())->delete();

        return back();
    }
}
