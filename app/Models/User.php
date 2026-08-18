<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'jlpt_level', 'university', 'study_program', 'bio', 'avatar_url'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class);
    }

    public function grammarPatterns(): HasMany
    {
        return $this->hasMany(GrammarPattern::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TranslationExercise::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(ScheduleItem::class);
    }

    public function jlptTargets(): HasMany
    {
        return $this->hasMany(JlptTarget::class);
    }

    public function planTasks(): HasMany
    {
        return $this->hasMany(PlanTask::class);
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function campusDiaries(): HasMany
    {
        return $this->hasMany(CampusDiary::class);
    }

    public function campusPhotos(): HasMany
    {
        return $this->hasMany(CampusPhoto::class);
    }

    public function jlptMockResults(): HasMany
    {
        return $this->hasMany(JlptMockResult::class);
    }

    public function courseGrades(): HasMany
    {
        return $this->hasMany(CourseGrade::class);
    }

    public function thesisProfile(): HasOne
    {
        return $this->hasOne(ThesisProfile::class);
    }

    public function thesisMilestones(): HasMany
    {
        return $this->hasMany(ThesisMilestone::class);
    }
}
