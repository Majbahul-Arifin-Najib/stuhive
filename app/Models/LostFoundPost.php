<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['post_id', 'item_name', 'location', 'is_found', 'found_at', 'image_path'])]
class LostFoundPost extends Model
{
    use BelongsToPost, HasFactory;

    protected $attributes = [
        'is_found' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_found' => 'boolean',
            'found_at' => 'datetime',
        ];
    }
}
