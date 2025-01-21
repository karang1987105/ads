<?php

namespace App\Services\Vast;

class CreativeExtensions extends Node {
    public function __construct() {
        parent::__construct("CreativeExtensions");
    }public function addCreativeExtension($content, $type = "") {
        $item = new Node("CreativeExtension");
$item->type = $type;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}