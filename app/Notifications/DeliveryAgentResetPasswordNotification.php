<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryAgentResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور')
            ->line('لقد طلبت إعادة تعيين كلمة المرور.')
            ->line('رمز إعادة التعيين هو: ' . $this->token)
            ->line('هذا الرمز صالح لمدة 60 دقيقة.')
            ->line('إذا لم تطلب إعادة تعيين كلمة المرور، فتجاهل هذا البريد.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
