<?php

namespace App\Services\Vast;

class NonLinear extends Node {
    public function __construct() {
        parent::__construct("NonLinear");
    }public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
        return $this;
    }
public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
        return $this;
    }
public function getExpandedWidth() {
        return $this->expandedWidth;
    }

    public function setExpandedWidth($expandedWidth) {
        $this->expandedWidth = $expandedWidth;
        return $this;
    }
public function getExpandedHeight() {
        return $this->expandedHeight;
    }

    public function setExpandedHeight($expandedHeight) {
        $this->expandedHeight = $expandedHeight;
        return $this;
    }
public function getScalable() {
        return $this->scalable;
    }

    public function setScalable($scalable) {
        $this->scalable = $scalable;
        return $this;
    }
public function getMaintainAspectRatio() {
        return $this->maintainAspectRatio;
    }

    public function setMaintainAspectRatio($maintainAspectRatio) {
        $this->maintainAspectRatio = $maintainAspectRatio;
        return $this;
    }
public function getApiFramework() {
        return $this->apiFramework;
    }

    public function setApiFramework($apiFramework) {
        $this->apiFramework = $apiFramework;
        return $this;
    }
public function getMinSuggestedDuration() {
        return $this->minSuggestedDuration;
    }

    public function setMinSuggestedDuration($minSuggestedDuration) {
        $this->minSuggestedDuration = $minSuggestedDuration;
        return $this;
    }public function setNonLinearClickThrough($content) {
        $item = new Node("NonLinearClickThrough");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setNonLinearClickTracking($content, $id = "") {
        $item = new Node("NonLinearClickTracking");
$item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addStaticResource($creativeType, $content) {
        $item = new Node("StaticResource");
$item->creativeType = $creativeType;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addIFrameResource($content) {
        $item = new Node("IFrameResource");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addHTMLResource($content) {
        $item = new Node("HTMLResource");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setAdParameters($content, $xmlEncoded = "") {
        $item = new Node("AdParameters");
$item->xmlEncoded = $xmlEncoded;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}