<?php

namespace App\Services\Vast;

class IconClickFallbackImage extends Node {
    public function __construct() {
        parent::__construct("IconClickFallbackImage");
    }public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
        return $this;
    }
public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
        return $this;
    }public function setAltText($content) {
        $item = new Node("AltText");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addStaticResource($creativeType, $content) {
        $item = new Node("StaticResource");
$item->creativeType = $creativeType;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}