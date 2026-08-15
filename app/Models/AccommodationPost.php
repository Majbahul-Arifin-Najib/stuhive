<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['post_id', 'area', 'walking_distance', 'phone_number', 'rent', 'image_path'])]
class AccommodationPost extends Model
{
    use BelongsToPost, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rent' => 'decimal:2',
        ];
    }
}
