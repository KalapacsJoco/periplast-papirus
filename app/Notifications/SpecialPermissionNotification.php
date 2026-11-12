<?php

namespace App\Notifications;

use App\Models\SpecialPermissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpecialPermissionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly SpecialPermissionRequest $request,
        private readonly string $event,
        private readonly string $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request->loadMissing(['product', 'order.machine', 'photos']);
        $t = fn (string $key, array $replace = []): string => trans('dashboard.'.$key, $replace, 'hu');

        $message = (new MailMessage)
            ->subject($t('special_mail_subject', ['product' => $request->product?->name]))
            ->greeting($t('special_mail_greeting'))
            ->line($t('special_mail_intro', ['actor' => $this->actor]))
            ->line($t('special_mail_product', ['product' => $request->product?->name.' ('.$request->product?->code.')']))
            ->line($t('special_mail_order', ['order' => $request->order?->order_number ?? '-', 'machine' => $request->order?->machine?->name ?? '-']))
            ->line($t('special_mail_reason', ['reason' => $request->reason]));

        foreach ($request->photos as $index => $photo) {
            $message->line($t('special_mail_photo', ['n' => $index + 1, 'url' => $photo->url()]));
        }

        return $message->action($t('special_mail_action'), url('/'));
    }

    /** @return array<string, int|string|null> */
    public function toArray(object $notifiable): array
    {
        $request = $this->request->loadMissing(['product', 'order']);

        return [
            'special_permission_request_id' => $request->id,
            'order_id' => $request->order_id,
            'order_number' => $request->order?->order_number,
            'product' => $request->product?->name,
            'event' => $this->event,
            'actor' => $this->actor,
        ];
    }
}
