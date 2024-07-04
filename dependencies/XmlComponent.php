<?php
/*
    Author: kontakt@leondierkes.de
    Description: Handles XML data
*/

class XmlComponent {
    public function parseXML($filepath) {
		try {
            $XmlValidator = XMLReader::open($filepath);
            $XmlValidator->setParserProperty(XMLReader::VALIDATE, true);

            if(!$XmlValidator->isValid())
                throw new Exception("Invalid file format, not xml.");

            // XML to PHP Array conversion for better data handling
            $xmlElement = simplexml_load_string(file_get_contents($filepath), SimpleXMLElement::class, LIBXML_NOCDATA);

            if($xmlElement === false)
                throw new Exception("Invalid file format, not xml.");

            $xmlElement = json_encode($xmlElement);
            $xmlElement = json_decode($xmlElement, true);

            return $xmlElement;
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
}
?>