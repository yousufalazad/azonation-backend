<?php

namespace App\Notifications;

use App\Models\SupportRequest;
use Illuminate\Notifications\Notification;

// Tells the person who asked for help that the Azonation team has answered
class SupportReplied extends Notification
{
    public function __construct(public SupportRequest $supportRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'support_replied',
            'title' => 'Azonation support replied',
            'message' => "We have answered your request: {$this->supportRequest->subject}",
            'support_request_id' => $this->supportRequest->id,
            'url' => match ($notifiable->type) {
                'organisation' => "/org-dashboard/support/{$this->supportRequest->id}",
                'individual' => "/individual-dashboard/support/{$this->supportRequest->id}",
                default => null,
            },
        ];
    }
}
