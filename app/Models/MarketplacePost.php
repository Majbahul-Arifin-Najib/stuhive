<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['post_id', 'product_name', 'price', 'condition', 'contact_number', 'is_sold', 'image_path'])]
class MarketplacePost extends Model
{
    use BelongsToPost, HasFactory;

    protected $attributes = [
        'condition' => 'used',
        'is_sold' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_sold' => 'boolean',
        ];
    }
}
