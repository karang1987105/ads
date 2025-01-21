<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUpdate extends Notification implements ShouldQueue {
    use Queueable;

    public string $type;
    public array $data = [];

    public static string $TYPE_ACCOUNT_ACTIVATED = 'account_activated';
    public static string $TYPE_ACCOUNT_SUSPENDED = 'account_suspended';
    public static string $TYPE_DOMAIN_APPROVED = 'domain_approved';
    public static string $TYPE_DOMAIN_DECLINED = 'domain_declined';
    public static string $TYPE_PLACE_APPROVED = 'place_approved';
    public static string $TYPE_PLACE_DECLINED = 'place_declined';
    public static string $TYPE_AD_APPROVED = 'ad_approved';
    public static string $TYPE_AD_DECLINED = 'ad_declined';
    public static string $TYPE_CAMPAIGN_EXPIRED = 'campaign_expired';
    public static string $TYPE_WITHDRAWAL_CONFIRMATION = 'withdrawal_confirmation';
    public static string $TYPE_WITHDRAWAL_CONFIRMED = 'withdrawal_confirmed';
    public static string $TYPE_WITHDRAWAL_PAID = 'withdrawal_paid';

    private static array $INFO = [
        'account_suspended' => [
            'subject' => 'Your account has been suspended!',
            'template' => 'mail.user-update.account-suspended'
        ],
        'account_activated' => [
            'subject' => 'Your account has been activated!',
            'template' => 'mail.user-update.account-activated'
        ],
        'domain_approved' => [
            'subject' => 'Your domain has been approved!',
            'template' => 'mail.user-update.domain-approved'
        ],
        'domain_declined' => [
            'subject' => 'Your domain has been declined!',
            'template' => 'mail.user-update.domain-declined'
        ],
        'place_approved' => [
            'subject' => 'Your place has been approved!',
            'template' => 'mail.user-update.place-approved'
        ],
        'place_declined' => [
            'subject' => 'Your place has been declined!',
            'template' => 'mail.user-update.place-declined'
        ],
        'ad_approved' => [
            'subject' => 'Your ad has been approved!',
            'template' => 'mail.user-update.ad-approved'
        ],
        'ad_declined' => [
            'subject' => 'Your ad has been declined!',
            'template' => 'mail.user-update.ad-declined'
        ],
        'campaign_expired' => [
            'subject' => 'Your campaign has been expired!',
            'template' => 'mail.user-update.campaign-expired'
        ],
        'withdrawal_confirmation' => [
            'subject' => 'New withdrawal requested!',
            'template' => 'mail.user-update.withdrawal-request'
        ],
        'withdrawal_confirmed' => [
            'subject' => 'Withdrawal request confirmed!',
            'template' => 'mail.user-update.withdrawal-request-confirmed'
        ],
        'withdrawal_paid' => [
            'subject' => 'Withdrawal paid!',
            'template' => 'mail.user-update.withdrawal-paid'
        ]
    ];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $type, array $data = []) {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable) {
        $info = self::$INFO[$this->type];
        $data = array_merge(['name' => $notifiable->name], $this->data);

        return (new MailMessage)
            ->subject($info['subject'])
            ->markdown($info['template'], $data);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            //
        ];
    }
}
