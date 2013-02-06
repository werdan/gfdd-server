<?php
class XMLTools {

	public static function createEmptyXMLDoc() {
		
		$newXml=new XMLWriter();
		$newXml->openMemory();
		$newXml->startDocument('1.0','UTF-8');
		$newXml->startElement("root");
		$newXml->endElement();
		return $newXml->outputMemory(true);
	}

}