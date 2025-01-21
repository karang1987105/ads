<?php

namespace App\Services\Vast;

class Linear extends Node {
    public function __construct() {
        parent::__construct("Linear");
    }public function getSkipoffset() {
        return $this->skipoffset;
    }

    public function setSkipoffset($skipoffset) {
        $this->skipoffset = $skipoffset;
        return $this;
    }public function setDuration($content) {
        $item = new Node("Duration");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setMediaFiles(MediaFiles $mediaFiles) {
        $this->appendChild($mediaFiles);
        return $this;
    }
public function setAdParameters($content, $xmlEncoded = "") {
        $item = new Node("AdParameters");
$item->xmlEncoded = $xmlEncoded;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setTrackingEvents(TrackingEvents $trackingEvents) {
        $this->appendChild($trackingEvents);
        return $this;
    }
public function setVideoClicks(VideoClicks $videoClicks) {
        $this->appendChild($videoClicks);
        return $this;
    }
public function setIcons(Icons $icons) {
        $this->appendChild($icons);
        return $this;
    }
}