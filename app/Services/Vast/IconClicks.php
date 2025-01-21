<?php

namespace App\Services\Vast;

class IconClicks extends Node {
    public function __construct() {
        parent::__construct("IconClicks");
    }public function setIconClickThrough($content) {
        $item = new Node("IconClickThrough");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addIconClickTracking($content, $id = "") {
        $item = new Node("IconClickTracking");
$item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setIconClickFallbackImages(IconClickFallbackImages $iconClickFallbackImages) {
        $this->appendChild($iconClickFallbackImages);
        return $this;
    }
}