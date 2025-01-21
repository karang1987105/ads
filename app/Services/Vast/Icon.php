<?php

namespace App\Services\Vast;

class Icon extends Node {
    public function __construct() {
        parent::__construct("Icon");
    }public function getProgram() {
        return $this->program;
    }

    public function setProgram($program) {
        $this->program = $program;
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
public function getXPosition() {
        return $this->xPosition;
    }

    public function setXPosition($xPosition) {
        $this->xPosition = $xPosition;
        return $this;
    }
public function getYPosition() {
        return $this->yPosition;
    }

    public function setYPosition($yPosition) {
        $this->yPosition = $yPosition;
        return $this;
    }
public function getDuration() {
        return $this->duration;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
        return $this;
    }
public function getOffset() {
        return $this->offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }
public function getApiFramework() {
        return $this->apiFramework;
    }

    public function setApiFramework($apiFramework) {
        $this->apiFramework = $apiFramework;
        return $this;
    }
public function getPxratio() {
        return $this->pxratio;
    }

    public function setPxratio($pxratio) {
        $this->pxratio = $pxratio;
        return $this;
    }
public function getAltText() {
        return $this->altText;
    }

    public function setAltText($altText) {
        $this->altText = $altText;
        return $this;
    }
public function getHoverText() {
        return $this->hoverText;
    }

    public function setHoverText($hoverText) {
        $this->hoverText = $hoverText;
        return $this;
    }public function addIconViewTracking($content) {
        $item = new Node("IconViewTracking");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setIconClicks(IconClicks $iconClicks) {
        $this->appendChild($iconClicks);
        return $this;
    }
}