<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StorePaymentReceived extends Notification
{
    use Queueable;

    protected $storeName;

    public function __construct($storeName)
    {
        $this->storeName = $storeName;
    }

    public function via($notifiable)
    {
        return ['database']; // إشعار يخزن في قاعدة البيانات فقط (بدون إيميل)
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "تم استلام الدفع من المتجر: {$this->storeName}",
        ];
    }
}
