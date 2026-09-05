<?php

namespace App\Models;

use App\Enums\PostType;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'type', 'content'])]
class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function eventInterests(): HasMany
    {
        return $this->hasMany(EventInterest::class);
    }

    public function lostFound(): HasOne
    {
        return $this->hasOne(LostFoundPost::class);
    }

    public function candid(): HasOne
    {
        return $this->hasOne(CandidPost::class);
    }

    public function event(): HasOne
    {
        return $this->hasOne(EventPost::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(NotePost::class);
    }

    public function pet(): HasOne
    {
        return $this->hasOne(PetPost::class);
    }

    public function courseReview(): HasOne
    {
        return $this->hasOne(CourseReviewPost::class);
    }

    public function marketplace(): HasOne
    {
        return $this->hasOne(MarketplacePost::class);
    }

    public function accommodation(): HasOne
    {
        return $this->hasOne(AccommodationPost::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(ConsultationPost::class);
    }

    public function studyGroup(): HasOne
    {
        return $this->hasOne(StudyGroup::class);
    }

    public function scopeOfType(Builder $query, PostType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /**
     * Restrict a feed to the sections the given role is allowed to browse.
     */
    public function scopeVisibleToRole(Builder $query, ?Role $role): Builder
    {
        if ($role !== Role::Faculty) {
            return $query;
        }

        $hidden = array_map(
            fn (PostType $type) => $type->value,
            array_filter(PostType::cases(), fn (PostType $type) => $type->hiddenFromFaculty())
        );

        return $query->whereNotIn('type', $hidden);
    }

    /**
     * The type-specific row that carries this post's extra columns.
     */
    public function detail(): ?Model
    {
        return $this->getRelationValue($this->type->detailRelation());
    }

    public function reactionSummary(): array
    {
        return $this->reactions
            ->groupBy('emoji')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->all();
    }
}
