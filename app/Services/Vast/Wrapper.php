<?php

namespace App\Services\Vast;

class Wrapper extends Node {
    public function __construct() {
        parent::__construct("Wrapper");
    }public function addImpression($content, $id = "") {
        $item = new Node("Impression");
$item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setVASTAdTagURI($content) {
        $item = new Node("VASTAdTagURI");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setAdSystem($content) {
        $item = new Node("AdSystem");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setPricing($model, $currency, $content) {
        $item = new Node("Pricing");
$item->model = $model;
$item->currency = $currency;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addError($content) {
        $item = new Node("Error");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setViewableImpression(ViewableImpression $viewableImpression) {
        $this->appendChild($viewableImpression);
        return $this;
    }
public function setAdVerifications(AdVerifications $adVerifications) {
        $this->appendChild($adVerifications);
        return $this;
    }
public function setExtensions(Extensions $extensions) {
        $this->appendChild($extensions);
        return $this;
    }
public function setCreatives(Creatives $creatives) {
        $this->appendChild($creatives);
        return $this;
    }
public function addBlockedAdCategories($authority, $content) {
        $item = new Node("BlockedAdCategories");
$item->authority = $authority;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}