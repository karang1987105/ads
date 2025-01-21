<?php

namespace App\Services\Vast;

class VideoClicks extends Node {
    public function __construct() {
        parent::__construct("VideoClicks");
    }
    
    public function setClickThrough($content, $id = "") {
        $item = new Node("ClickThrough");
        $item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setClickTracking($content, $id = "") {
        $item = new Node("ClickTracking");
        $item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setCustomClick($content, $id = "") {
        $item = new Node("CustomClick");
        $item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}