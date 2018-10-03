<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WorkStart extends Notification
{
    use Queueable;


    private $user;
    public function __construct(User $user)
    {
        $this->user=$user;
    }


    public function via($notifiable)
    {
        return ['broadcast','database'];
    }


    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }


    public function toArray($notifiable)
    {
        return [
            'message'=>$this->user->name.' has started working',
            'user_id'=>$this->user->id
        ];
    }
}
