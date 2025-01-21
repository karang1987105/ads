<?php

namespace App\Services\Vast;

class InLine extends Node {
    public function __construct() {
        parent::__construct("InLine");
    }

    public function setAdSystem($content) {
        $item = new Node("AdSystem");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setAdTitle($content) {
        $item = new Node("AdTitle");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function addImpression($content, $id = "") {
        $item = new Node("Impression");
        $item->id = $id;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setAdServingId($content) {
        $item = new Node("AdServingId");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function addCategory($authority, $content) {
        $item = new Node("Category");
        $item->authority = $authority;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setDescription($content) {
        $item = new Node("Description");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }

    public function setAdvertiser($content, $id = "") {
        $item = new Node("Advertiser");
        $item->id = $id;
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

    public function addSurvey($content, $type = "") {
        $item = new Node("Survey");
        $item->type = $type;
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

    public function setExtensions(Extensions $extensions) {
        $this->appendChild($extensions);
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

    public function setCreatives(Creatives $creatives) {
        $this->appendChild($creatives);
        return $this;
    }

    public function setExpires($content) {
        $item = new Node("Expires");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}