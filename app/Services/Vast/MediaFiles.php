<?php

namespace App\Services\Vast;

class MediaFiles extends Node {
    public function __construct() {
        parent::__construct("MediaFiles");
    }public function addMediaFile($delivery, $type, $width, $height, $content, $codec = "", $id = "", $bitrate = "", $minBitrate = "", $maxBitrate = "", $scalable = "", $maintainAspectRatio = "", $fileSize = "", $mediaType = "") {
        $item = new Node("MediaFile");
$item->delivery = $delivery;
$item->type = $type;
$item->width = $width;
$item->height = $height;
$item->codec = $codec;
$item->id = $id;
$item->bitrate = $bitrate;
$item->minBitrate = $minBitrate;
$item->maxBitrate = $maxBitrate;
$item->scalable = $scalable;
$item->maintainAspectRatio = $maintainAspectRatio;
$item->fileSize = $fileSize;
$item->mediaType = $mediaType;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addMezzanine($delivery, $type, $width, $height, $content, $codec = "", $id = "", $fileSize = "", $mediaType = "") {
        $item = new Node("Mezzanine");
$item->delivery = $delivery;
$item->type = $type;
$item->width = $width;
$item->height = $height;
$item->codec = $codec;
$item->id = $id;
$item->fileSize = $fileSize;
$item->mediaType = $mediaType;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addInteractiveCreativeFile($content, $type = "", $apiFramework = "", $variableDuration = "") {
        $item = new Node("InteractiveCreativeFile");
$item->type = $type;
$item->apiFramework = $apiFramework;
$item->variableDuration = $variableDuration;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setClosedCaptionFiles(ClosedCaptionFiles $closedCaptionFiles) {
        $this->appendChild($closedCaptionFiles);
        return $this;
    }
}