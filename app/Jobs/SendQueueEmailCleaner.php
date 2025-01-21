<?php


namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;

class SendQueueEmailCleaner implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $attachmentDir;

    public function __construct(string $attachmentDir) {
        $this->attachmentDir = $attachmentDir;
    }

    public function handle() {
        Log::info('SendQueueEmailCleaner handle for: ' . $this->attachmentDir);
        Storage::deleteDirectory($this->attachmentDir);
    }
}
