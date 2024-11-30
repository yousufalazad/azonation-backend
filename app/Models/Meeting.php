<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'short_name',
        'subject',
        'date',
        'start_time',
        'end_time',
        'meeting_type',
        'timezone',
        'meeting_mode',
        'duration',
        'priority',
        'video_conference_link',
        'access_code',
        'recording_link',
        'meeting_host',
        'max_participants',
        'rsvp_status',
        'participants',
        'description',
        'address',
        'agenda',
        'requirements',
        'note',
        'tags',
        'reminder_time',
        'repeat_frequency',
        'attachment',
        'conduct_type_id',
        'privacy_setup_id',
        'is_active',
        'visibility',
        'cancellation_reason',
        'feedback_link',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Define the relationship to the user who created the meeting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship to the conduct type.
     */
    public function conductType()
    {
        return $this->belongsTo(ConductType::class);
    }

    /**
     * Accessor for tags (JSON decoded).
     */
    public function getTagsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for tags (JSON encoded).
     */
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = json_encode($value);
    }

    /**
     * Accessor for participants (JSON decoded).
     */
    public function getParticipantsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for participants (JSON encoded).
     */
    public function setParticipantsAttribute($value)
    {
        $this->attributes['participants'] = json_encode($value);
    }

    /**
     * Accessor for RSVP status (JSON decoded).
     */
    public function getRsvpStatusAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for RSVP status (JSON encoded).
     */
    public function setRsvpStatusAttribute($value)
    {
        $this->attributes['rsvp_status'] = json_encode($value);
    }
}
