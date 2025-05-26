<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StorePaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $storeName;
    private $amount;

    public function __construct($storeName, $amount)
    {
        $this->storeName = $storeName;
        $this->amount = $amount;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('تم استلام دفعة جديدة')
            ->line('تم استلام دفعة جديدة من المتجر: ' . $this->storeName)
            ->line('المبلغ المدفوع: ' . $this->amount . ' ريال')
            ->line('يرجى مراجعة لوحة التحكم للموافقة النهائية على المتجر.');
    }
} 