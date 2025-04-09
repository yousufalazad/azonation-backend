<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;


class AddMemberSuccess extends Notification implements ShouldQueue
{
    use Queueable;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data=$data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/login'))
                    ->line('Thank you for using our application!');
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //'data' =>' You are added in '. Auth::user()->name 'successful'
            //'data' => 'You have been added to the organization: ' . $this->data['name'] . ' by ' . Auth::user()->name . ' successfully.'
            'data' => 'You have been added to the organization:  successfully.'

            // 'data' =>' You are added in xyz organisation successfully'

            // 'data' =>' You are added in '. $this->data.'successful'
        ];
    }
}

