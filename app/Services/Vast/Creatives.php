<?php

namespace App\Services\Vast;

class Creatives extends Node {
    public function __construct() {
        parent::__construct("Creatives");
    }public function addCreative(Creative $creative) {
        $this->appendChild($creative);
        return $this;
    }
}