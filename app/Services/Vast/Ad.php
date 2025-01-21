<?php

namespace App\Services\Vast;

class Ad extends Node {
    public function __construct() {
        parent::__construct("Ad");
    }public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
public function getSequence() {
        return $this->sequence;
    }

    public function setSequence($sequence) {
        $this->sequence = $sequence;
        return $this;
    }
public function getAdType() {
        return $this->adType;
    }

    public function setAdType($adType) {
        $this->adType = $adType;
        return $this;
    }public function setInLine(InLine $inLine) {
        $this->appendChild($inLine);
        return $this;
    }
public function setWrapper(Wrapper $wrapper) {
        $this->appendChild($wrapper);
        return $this;
    }
}
