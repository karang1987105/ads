<?php

namespace App\Services\Vast;

class Creative extends Node {
    public function __construct() {
        parent::__construct("Creative");
    }public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
public function getAdid() {
        return $this->adid;
    }

    public function setAdid($adid) {
        $this->adid = $adid;
        return $this;
    }
public function getSequence() {
        return $this->sequence;
    }

    public function setSequence($sequence) {
        $this->sequence = $sequence;
        return $this;
    }
public function getApiFramework() {
        return $this->apiFramework;
    }

    public function setApiFramework($apiFramework) {
        $this->apiFramework = $apiFramework;
        return $this;
    }public function addUniversalAdId($idRegistry, $content) {
        $item = new Node("UniversalAdId");
$item->idRegistry = $idRegistry;
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function setCreativeExtensions(CreativeExtensions $creativeExtensions) {
        $this->appendChild($creativeExtensions);
        return $this;
    }
public function setLinear(Linear $linear) {
        $this->appendChild($linear);
        return $this;
    }
public function setNonLinearAds(NonLinearAds $nonLinearAds) {
        $this->appendChild($nonLinearAds);
        return $this;
    }
public function setCompanionAds(CompanionAds $companionAds) {
        $this->appendChild($companionAds);
        return $this;
    }
}