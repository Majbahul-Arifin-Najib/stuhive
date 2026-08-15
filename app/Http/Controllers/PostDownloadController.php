<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostDownloadController extends Controller
{
    /**
     * Streams the image attached to a post so students can download any image
     * they can see in the application.
     */
    public function image(Post $post): StreamedResponse
    {
        Gate::authorize('view', $post);

        $post->load($post->type->detailRelation());
        $path = $post->detail()?->image_path;

        abort_if(blank($path), 404);

        $disk = Storage::disk(config('stuhive.uploads.disk'));

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, Str::slug($post->type->value.'-'.$post->id).'.'.pathinfo($path, PATHINFO_EXTENSION));
    }
}
