<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class GuestTicketReply extends Notification {
    use Queueable;

    private int $key;
    private array $messages;
    private string $replyUrl;
    private string $subject;

    public function __construct($key, $messages, $replyUrl, $subject = null, bool $closed = false) {
        $this->key = $key;
        $this->messages = $messages;
        $this->replyUrl = $replyUrl;
        $this->subject = $subject ?? ($closed ? '[Closed]' : '') . '[Ticket#' . $this->key . '] There is an update for you ticket on ' . config('app.name');
    }

    public function via($notifiable) {
        return ['mail'];
    }

    public function toMail($notifiable) {
        $m = new MailMessage;
        $m->subject($this->subject);
        $m->line("There is an update for Ticket#" . $this->key);
        foreach ($this->messages as $message) {
            $m->line(new HtmlString("<p>" . (isset($message['user']) ? "<i>$message[user]</i> " : "") .
                "<small>at $message[time]</small>:<br/>" . nl2br($message['content']) . "</p>"));
        }
        $m->line("");
        $m->action('Reply', $this->replyUrl);
        return $m;
    }

    public function toArray($notifiable) {
        return [];
    }
}
