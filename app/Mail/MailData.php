<?php


namespace App\Mail;


class MailData {
    private array $to;
    private string $subject;
    private string $body;
    private array $attachments = [];
    private array $inlineAttachments = [];

    public function setRecipient(string $address, string $name = null) {
        $this->to = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function setSubject(string $subject) {
        $this->subject = $subject;
        return $this;
    }

    public function setBody(string $body) {
        $this->body = $body;
        return $this;
    }

    public function addAttachment(string $path, $name, $mime) {
        $this->attachments[] = compact('path', 'name', 'mime');
        return $this;
    }

    public function addInlineAttachment(string $path, $name) {
        $this->inlineAttachments[] = compact('path', 'name');
        return $this;
    }

    public function getRecipientAddress(): string {
        return $this->to['address'];
    }

    public function getRecipientName(): string|null {
        return $this->to['name'];
    }

    public function getSubject(): string {
        return $this->subject;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function hasAttachment(): bool {
        return !empty($this->attachments);
    }

    public function getAttachments(): array {
        return $this->attachments;
    }

    public function hasInlineAttachment(): bool {
        return !empty($this->inlineAttachments);
    }

    public function getInlineAttachments(): array {
        return $this->inlineAttachments;
    }
}
