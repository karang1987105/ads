<?php

namespace App\Jobs;

use App\Helpers\Helper;
use App\Mail\MailData;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Markdown;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use Storage;

class SendQueueEmail implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private MailData $data;

    public function __construct(MailData $data) {
        $this->data = $data;
    }

    public function handle() {
        Mail::send([], [], function (Message $message) {
            $message->from(config('ads.email.from.email'), config('ads.email.from.name'));
            $message->subject($this->data->getSubject());
            $message->to($this->data->getRecipientAddress(), $this->data->getRecipientName());

            if (config('ads.email.reply_to.email') !== null) {
                $message->replyTo(config('ads.email.reply_to.email'), config('ads.email.reply_to.name'));
            }

            $html = $this->data->getBody();
            if ($this->data->hasInlineAttachment()) {
                foreach ($this->data->getInlineAttachments() as $inlineAttachment) {
                    $cid = $message->embed(Storage::path($inlineAttachment['path']));
                    $html = Helper::phx('attach-' . $inlineAttachment['name'], '<img src="' . $cid . '" alt="img"/>', $html);
                }
            }
            foreach ($this->data->getAttachments() as $attachment) {
                $message->attach(Storage::path($attachment['path']), ['as' => $attachment['name'], 'mime' => $attachment['mime']]);
            }

            $markdown = Container::getInstance()->make(Markdown::class);
            $body = $markdown->theme($markdown->getTheme())->render('components.emailstemplates.email', ['body' => $html]);
            $message->setBody($body, 'text/html');
        });
    }
}
