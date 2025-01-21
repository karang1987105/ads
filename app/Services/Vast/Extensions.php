<?php

namespace App\Services\Vast;

class Extensions extends Node {
    public function __construct() {
        parent::__construct("Extensions");
    }public function addExtension($content, $type = "") {
        $item = new Node("Extension");
$item->type = $type;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}