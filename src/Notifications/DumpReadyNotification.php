<?php

declare(strict_types=1);

namespace WebMavens\Triki\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DumpReadyNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $status,
        protected ?string $errorMessage = null,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->status === 'success') {
            return (new MailMessage)
                        ->line('Your Database Dump is Ready.')
                        ->action('Download', url('/triki/download'))
                        ->line('Thank you for using our application!');
        }

        // If the status is 'failure', send the error message in the email
        return (new MailMessage)
                    ->line('There was an error while generating your Database Dump.')
                    ->line("Error Message: {$this->errorMessage}")
                    ->line('Please check the logs or try again later.')
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
            'status'        => $this->status,
            'error_message' => $this->errorMessage,
        ];
    }
}
