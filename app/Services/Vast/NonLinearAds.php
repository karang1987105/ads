<?php

namespace App\Services\Vast;

class NonLinearAds extends Node {
    public function __construct() {
        parent::__construct("NonLinearAds");
    }public function setNonLinear(NonLinear $nonLinear) {
        $this->appendChild($nonLinear);
        return $this;
    }
}