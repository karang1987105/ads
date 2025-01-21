<?php

namespace App\Services\Vast;

class ClosedCaptionFiles extends Node {
    public function __construct() {
        parent::__construct("ClosedCaptionFiles");
    }public function addClosedCaptionFile($content, $type = "", $language = "") {
        $item = new Node("ClosedCaptionFile");
$item->type = $type;
$item->language = $language;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}