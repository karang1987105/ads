<?php

namespace App\Services\Vast;

class AdVerifications extends Node {
    public function __construct() {
        parent::__construct("AdVerifications");
    }public function setVerification(Verification $verification) {
        $this->appendChild($verification);
        return $this;
    }
}