<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['post_id', 'pet_name', 'spotted_at', 'image_path'])]
class PetPost extends Model
{
    use BelongsToPost, HasFactory;
}
