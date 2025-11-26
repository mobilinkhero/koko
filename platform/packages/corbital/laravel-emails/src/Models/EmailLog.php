<?php

namespace Corbital\LaravelEmails\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_template_id',  // Updated from template_id
        'subject',
        'from',
        'to',
        'cc',
        'bcc',
        'reply_to',
        'data',
        'status',            // Added to match schema
        'error',
        'sent_at',
        'scheduled_at',      // Updated from scheduled_for
        'is_test',
        'is_opened',
        'opened_at',
        'is_clicked',
        'clicked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',  // Updated from scheduled_for
        'is_test' => 'boolean',   // Added
        'is_opened' => 'boolean',
        'opened_at' => 'datetime',
        'is_clicked' => 'boolean',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the template that owns the log.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Scope a query to only include successful emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope a query to only include failed emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope a query to only include sent emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope a query to only include scheduled emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->whereNull('sent_at')->whereNotNull('scheduled_for');
    }

    /**
     * Scope a query to only include pending scheduled emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingScheduled($query)
    {
        return $query->whereNull('sent_at')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now());
    }

    /**
     * Scope a query to only include opened emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpened($query)
    {
        return $query->where('is_opened', true);
    }

    /**
     * Scope a query to only include clicked emails.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClicked($query)
    {
        return $query->where('is_clicked', true);
    }

    /**
     * Get a summary of recipients.
     */
    public function getRecipientsListAttribute(): string
    {
        $recipients = json_decode($this->recipients, true);
        if (! $recipients) {
            return '';
        }

        return collect($recipients)
            ->map(function ($recipient) {
                if (isset($recipient['name']) && $recipient['name']) {
                    return "{$recipient['name']} <{$recipient['email']}>";
                }

                return $recipient['email'];
            })
            ->implode(', ');
    }
}
