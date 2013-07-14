<?php
$xml = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<books>
<book>Patterns of Enterprise Application Architecture <bookit> Yo</bookit> </book>
<book>Design Patterns: Elements of Reusable Software Design <bookit> Yipee </bookit> </book>
<book>Clean Code</book>
</books>
XML;

$dom = new DOMDocument;
$dom->loadXML($xml);
$books = $dom->getElementsByTagName('bookit');
foreach ($books as $book) {
    echo $book->nodeValue."<br>";
}
?>
