<?php


namespace App\Services\Vast;

use XMLWriter;

class Node {
    protected $name;
    protected $attributes = [];
    protected $textContent;
    protected $children = [];

    public function __construct($name, $content = null) {
        $this->name = $name;
        $this->textContent = $content;
    }

    public function setContent($value) {
        $this->textContent = $value;
    }

    public function getContent() {
        return $this->textContent;
    }

    public function __get($name) {
        return $this->attributes[$name];
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __isset($name) {
        return isset($this->attributes[$name]);
    }

    public function __toString() {
        return $this->getXML();
    }

    protected function appendChild(Node $node) {
        $this->children[] = $node;
    }

    public function getXML(): string {
        $xw = new XMLWriter();
        $xw->openMemory();
        $this->toXML($xw);
        return $xw->outputMemory();
    }

    protected function toXML(XMLWriter $xw) {
        $xw->startElement($this->name);
        foreach ($this->attributes as $name => $value) {
            $xw->writeAttribute($name, $value);
        }
        if (isset($this->textContent)) {
            $xw->writeCdata($this->textContent);
        } else {
            foreach ($this->children as $value) {
                $value->toXML($xw);
            }
        }
        $xw->endElement();
    }
}