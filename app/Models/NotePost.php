<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

#[Fillable(['post_id', 'title', 'course_code', 'faculty_initial', 'file_path', 'original_filename', 'file_size'])]
class NotePost extends Model
{
    use BelongsToPost, HasFactory;

    public function readableSize(): string
    {
        return Number::fileSize($this->file_size);
    }
}
