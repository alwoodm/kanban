<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'order',
        'column_id',
        'due_date',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * Dostępne priorytety kart
     */
    public const PRIORITY_LOW = 'niski';
    public const PRIORITY_MEDIUM = 'średni';
    public const PRIORITY_HIGH = 'wysoki';

    /**
     * Get the column that owns the card.
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    /**
     * The users that are assigned to the card.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * The labels that are attached to the card.
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class)
            ->withTimestamps();
    }

    /**
     * Get the comments for the card.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
