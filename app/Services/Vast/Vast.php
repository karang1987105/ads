<?php

namespace App\Services\Vast;

use App\Services\Vast\Ad;
use App\Services\Vast\Node;
use XMLWriter;

class Vast extends Node {
    public function __construct() {
        parent::__construct("VAST");
        $this->version = '4.2';
    }

    public function addError($content = null) {
        $item = new Node("Error");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function addAd(Ad $ad) {
        $this->appendChild($ad);
        return $this;
    }

    public function getXML(): string {
        $xw = new XMLWriter();
        $xw->openMemory();
        $xw->setIndent(true);
        $xw->setIndentString("\t");
        $xw->startDocument("1.0", "UTF-8");
        $this->toXML($xw);
        $xw->endDocument();
        $content = $xw->outputMemory();
        $xw = null;
        return $content;
    }
}
