<?php
class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($cdata_text) {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }

    public function addChildCData($element_name, $cdata) {
        $this->$element_name = NULL;
        $this->$element_name->addCData($cdata);
    }
}