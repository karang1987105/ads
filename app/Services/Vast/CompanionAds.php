<?php

namespace App\Services\Vast;

class CompanionAds extends Node {
    public function __construct() {
        parent::__construct("CompanionAds");
    }public function getRequired() {
        return $this->required;
    }

    public function setRequired($required) {
        $this->required = $required;
        return $this;
    }public function addCompanion(Companion $companion) {
        $this->appendChild($companion);
        return $this;
    }
}