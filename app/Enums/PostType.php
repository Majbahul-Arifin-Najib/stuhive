<?php

namespace App\Enums;

use App\Models\AccommodationPost;
use App\Models\CandidPost;
use App\Models\ConsultationPost;
use App\Models\CourseReviewPost;
use App\Models\EventPost;
use App\Models\LostFoundPost;
use App\Models\MarketplacePost;
use App\Models\NotePost;
use App\Models\PetPost;
use App\Models\StudyGroup;
use Illuminate\Database\Eloquent\Model;

enum PostType: string
{
    case LostFound = 'lost_found';
    case Candid = 'candid';
    case Event = 'event';
    case Note = 'note';
    case Pet = 'pet';
    case CourseReview = 'course_review';
    case Marketplace = 'marketplace';
    case Accommodation = 'accommodation';
    case Consultation = 'consultation';
    case StudyGroup = 'study_group';

    public function label(): string
    {
        return match ($this) {
            self::LostFound => 'Lost & Found',
            self::Candid => 'Candid Sharing Wall',
            self::Event => 'Event Announcements',
            self::Note => 'Resources Library',
            self::Pet => 'Campus Pets',
            self::CourseReview => 'Course Discussion & Review',
            self::Marketplace => 'Marketplace',
            self::Accommodation => 'Accommodation',
            self::Consultation => 'Consultation Hub',
            self::StudyGroup => 'Study Group Finder',
        };
    }

    public function tagline(): string
    {
        return match ($this) {
            self::LostFound => 'Post what you lost, claim what you found.',
            self::Candid => 'Everyday campus moments, shared by students.',
            self::Event => 'Club events happening around campus.',
            self::Note => 'Shared lecture notes and PDF material.',
            self::Pet => 'The cats and dogs that own this campus.',
            self::CourseReview => 'Honest experiences of courses and faculty.',
            self::Marketplace => 'Buy and sell within the campus.',
            self::Accommodation => 'Rooms and flats near campus.',
            self::Consultation => 'Book a slot with your faculty.',
            self::StudyGroup => 'Find study partners for a course.',
        };
    }

    /**
     * URL segment used by this section, e.g. "lost-found".
     */
    public function slug(): string
    {
        return match ($this) {
            self::LostFound => 'lost-found',
            self::Candid => 'candid',
            self::Event => 'events',
            self::Note => 'resources',
            self::Pet => 'pets',
            self::CourseReview => 'course-reviews',
            self::Marketplace => 'marketplace',
            self::Accommodation => 'accommodations',
            self::Consultation => 'consultations',
            self::StudyGroup => 'study-groups',
        };
    }

    /**
     * Route name prefix for this section, e.g. "lost_found".
     */
    public function routePrefix(): string
    {
        return match ($this) {
            self::Note => 'resources',
            self::Event => 'events',
            self::Pet => 'pets',
            self::CourseReview => 'course_reviews',
            self::Accommodation => 'accommodations',
            self::Consultation => 'consultations',
            self::StudyGroup => 'study_groups',
            default => $this->value,
        };
    }

    public function indexRoute(): string
    {
        return $this->routePrefix().'.index';
    }

    public function icon(): string
    {
        return match ($this) {
            self::LostFound => 'magnifier',
            self::Candid => 'camera',
            self::Event => 'megaphone',
            self::Note => 'book',
            self::Pet => 'heart',
            self::CourseReview => 'star',
            self::Marketplace => 'tag',
            self::Accommodation => 'home',
            self::Consultation => 'chat',
            self::StudyGroup => 'users',
        };
    }

    /**
     * @return class-string<Model>
     */
    public function detailModel(): string
    {
        return match ($this) {
            self::LostFound => LostFoundPost::class,
            self::Candid => CandidPost::class,
            self::Event => EventPost::class,
            self::Note => NotePost::class,
            self::Pet => PetPost::class,
            self::CourseReview => CourseReviewPost::class,
            self::Marketplace => MarketplacePost::class,
            self::Accommodation => AccommodationPost::class,
            self::Consultation => ConsultationPost::class,
            self::StudyGroup => StudyGroup::class,
        };
    }

    /**
     * Name of the Post relationship holding this type's extra columns.
     */
    public function detailRelation(): string
    {
        return match ($this) {
            self::LostFound => 'lostFound',
            self::Candid => 'candid',
            self::Event => 'event',
            self::Note => 'note',
            self::Pet => 'pet',
            self::CourseReview => 'courseReview',
            self::Marketplace => 'marketplace',
            self::Accommodation => 'accommodation',
            self::Consultation => 'consultation',
            self::StudyGroup => 'studyGroup',
        };
    }

    public function allowsComments(): bool
    {
        return match ($this) {
            self::LostFound, self::Event, self::Note, self::Pet, self::CourseReview => true,
            default => false,
        };
    }

    public function allowsReactions(): bool
    {
        return $this !== self::Consultation && $this !== self::StudyGroup;
    }

    /**
     * The spec hides these sections from anyone signed in as faculty.
     */
    public function hiddenFromFaculty(): bool
    {
        return match ($this) {
            self::Candid, self::Note, self::CourseReview, self::StudyGroup, self::Accommodation => true,
            default => false,
        };
    }

    /**
     * @return array<int, Role>
     */
    public function authorRoles(): array
    {
        return match ($this) {
            self::Consultation => [Role::Faculty],
            self::Event => [Role::Student, Role::Admin],
            default => [Role::Student],
        };
    }

    public function visibleTo(?Role $role): bool
    {
        return $role !== Role::Faculty || ! $this->hiddenFromFaculty();
    }

    public function creatableBy(?Role $role): bool
    {
        return $role !== null && in_array($role, $this->authorRoles(), true);
    }
}
