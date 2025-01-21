<?php

namespace App\Services\Vast;

class TrackingEvents extends Node {
    public function __construct() {
        parent::__construct("TrackingEvents");
    }
    
    public function addTracking($content, $event = "", $offset = "") {
        $item = new Node("Tracking");
        $item->event = $event;
        $item->offset = $offset;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}