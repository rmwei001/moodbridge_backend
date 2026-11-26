<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    protected $signedUrl;

    public function __construct($signedUrl)
    {
        $this->signedUrl = $signedUrl;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your MoodBridge account')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for registering with MoodBridge.')
            ->line('Please verify your email address to activate your clinician account.')
            ->action('Verify Email', $this->signedUrl)
            ->line('This verification link expires in 60 minutes.')
            ->line('If you did not create a MoodBridge account, no action is required.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
