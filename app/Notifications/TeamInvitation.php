<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification
{
    use Queueable;

    public function __construct(
        protected Team $team,
        protected string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/api/v1/invitations/{$this->token}/accept");

        return (new MailMessage)
            ->subject('You are invited to join a team')
            ->greeting('Hello!')
            ->line("You have been invited to join {$this->team->name}.")
            ->action('Accept Invitation', $url)
            ->line('If you did not expect this invitation, you can ignore this email.');
    }
}
