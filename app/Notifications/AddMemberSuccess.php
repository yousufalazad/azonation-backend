<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


// class AddMemberSuccess extends Notification implements ShouldQueue
class AddMemberSuccess extends Notification
{
    use Queueable;

    public function __construct(
        public string $orgName,
        public ?string $actorName = null,
        public ?int $actorId = null,
    ) {}

    // DB only — no queue required
    public function via($notifiable): array
    {
        return ['database'];
    }

    
    public function toArray($notifiable): array
    {
        return [
            'type'       => 'member_added',
            'title'      => 'Added to organisation',
            'message'    => "You have been added to {$this->orgName}.",
            'org_name'   => $this->orgName,
            'actor_name' => $this->actorName,
            'actor_id'   => $this->actorId,
            // 'url'        => null, // e.g. route('members.index') if you have one
            'url'        => route('connected-organisations'),
        ];
    }
}
