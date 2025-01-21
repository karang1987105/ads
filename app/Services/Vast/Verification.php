<?php

namespace App\Services\Vast;

class Verification extends Node {
    public function __construct() {
        parent::__construct("Verification");
    }public function getVendor() {
        return $this->vendor;
    }

    public function setVendor($vendor) {
        $this->vendor = $vendor;
        return $this;
    }public function addJavaScriptResource($apiFramework, $browserOptional, $content) {
        $item = new Node("JavaScriptResource");
$item->apiFramework = $apiFramework;
$item->browserOptional = $browserOptional;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addExecutableResource($apiFramework, $type, $content) {
        $item = new Node("ExecutableResource");
$item->apiFramework = $apiFramework;
$item->type = $type;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setTrackingEvents(TrackingEvents $trackingEvents) {
        $this->appendChild($trackingEvents);
        return $this;
    }
public function setVerificationParameters($content) {
        $item = new Node("VerificationParameters");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}