<?php

namespace App\Services\Vast;

class ViewableImpression extends Node {
    public function __construct() {
        parent::__construct("ViewableImpression");
    }public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }public function addViewable($content) {
        $item = new Node("Viewable");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addNotViewable($content) {
        $item = new Node("NotViewable");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
public function addViewUndetermined($content) {
        $item = new Node("ViewUndetermined");
        $item->setContent($content);
        $this->appendChild($item);
        return $this;
    }
}