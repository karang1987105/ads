<?php

namespace App\Services\Vast;

class Companion extends Node {
    public function __construct() {
        parent::__construct("Companion");
    }public function getWidth() {
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
public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
public function getAssetWidth() {
        return $this->assetWidth;
    }

    public function setAssetWidth($assetWidth) {
        $this->assetWidth = $assetWidth;
        return $this;
    }
public function getAssetHeight() {
        return $this->assetHeight;
    }

    public function setAssetHeight($assetHeight) {
        $this->assetHeight = $assetHeight;
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
public function getApiFramework() {
        return $this->apiFramework;
    }

    public function setApiFramework($apiFramework) {
        $this->apiFramework = $apiFramework;
        return $this;
    }
public function getAdSlotId() {
        return $this->adSlotId;
    }

    public function setAdSlotId($adSlotId) {
        $this->adSlotId = $adSlotId;
        return $this;
    }
public function getPxratio() {
        return $this->pxratio;
    }

    public function setPxratio($pxratio) {
        $this->pxratio = $pxratio;
        return $this;
    }
public function getRenderingMode() {
        return $this->renderingMode;
    }

    public function setRenderingMode($renderingMode) {
        $this->renderingMode = $renderingMode;
        return $this;
    }public function addStaticResource($creativeType, $content) {
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
public function setAltText($content) {
        $item = new Node("AltText");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setCompanionClickThrough($content) {
        $item = new Node("CompanionClickThrough");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addCompanionClickTracking($content, $id = "") {
        $item = new Node("CompanionClickTracking");
$item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setTrackingEvents(TrackingEvents $trackingEvents) {
        $this->appendChild($trackingEvents);
        return $this;
    }
}