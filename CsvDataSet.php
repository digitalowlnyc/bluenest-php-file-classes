<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

class CsvDataSet implements Iterator {
    private $rowsArray;
    private $headerArray;
    private $currentRowIndex = 0;
    private $delimiter = ",";
    private $enclosureCharacter = "\"";
    private $escapeCharacter;
    const EMPTY_STRING = '';
    private $filename = null;
    const NULL_REFERENCE = null;

    function __construct($delimiter = ",", $enclosure = "\"", $escape = "\"") {
        $this->delimiter = $delimiter;
        $this->enclosureCharacter = $enclosure;
        $this->escapeCharacter = $escape;
    }

    static function fromFile($file, $delimiter = ",", $enclosure = "\"", $escape = "\"") {
        $n = new CsvDataSet($delimiter, $enclosure, $escape);
        $n->loadFile($file);
        return $n;
    }

    function setEscapeCharacter($char) {
        $this->escapeCharacter = $char;
    }

    function setEnclosureCharacter($char) {
        $this->enclosureCharacter = $char;
    }

    function setHeader($headerArray) {
        $this->headerArray = $headerArray;
    }

    function getValue($key) {
        if(array_key_exists($key, $this->rowsArray[$key])) {
            return $this->rowsArray[$key];
        } else {
            return static::EMPTY_STRING;
        }
    }

    function nextRow() {
        $this->currentRowIndex += 1;
    }

    function previousRow() {
        $this->currentRowIndex -= 1;
    }

    function save() {
        if($this->filename === null) {
            throw new \Exception('save() only valid when loading from file, use saveAs() instead and specify a filename');
        }
        return $this->saveAs($this->filename);
    }

    function saveAs($filename = null) {
        $fileWriter = new \CsvFileWriter($filename, $this->delimiter);
        $fileWriter->writeBOM(); // BND TODO DO NOT ASSUME BOM
        $fileWriter->addLineFromArray($this->headerArray);

        /** @var CsvRow $row */
        $i = 0;
        foreach($this->rowsArray as $row) {
            $outputRow = [];

            foreach($this->headerArray as $header) {
                if($row->hasMetaData()) {
                    $val = $row->getValueAndMetaData($header);
                    if($val === null) {
                        $val = EMPTY_STRING;
                    } else {
                        $val['data'] = str_replace($this->delimiter, $this->escapeCharacter . $this->delimiter, $val['data']);

                        if($val['enclosed']) {
                            $val = '"' . $val['data'] . '"'; // BND TODO enclosure char
                        } else {
                            $val = $val['data'];
                        }
                    }
                } else {
                    $val = $row[$header];
                }

                $outputRow[] = $val;
            }

            $fileWriter->addLineFromArray($outputRow);
            $i += 1;
        }

        $this->filename = $filename;

        $fileWriter->close();

        printMsg("Wrote rows: " . $i);

        return file_exists($filename);
    }

    function loadFile($filename) {
        // BND TODO Make params configurable
        $result = csvFileRead($filename,
                        /*$fileHasHeaders*/ true,
                        /*$useIndices = */ false,
                        /*$verbose = */ true,
                        /*$maxLineLength = */ null,
                        /*$delimiter = */ $this->delimiter,
            $this->enclosureCharacter,
            $this->escapeCharacter);

        $this->headerArray = $result['headers'];
        $this->rowsArray = $result['data'];
        $this->filename = $filename;
    }

    function deleteRow() {
        $this->rowsArray[$this->currentRowIndex] = null;
        //unset($this->rowsArray[$this->currentRowIndex]);
    }

    function purgeDeletedRows() {
        $this->rowsArray = array_filter($this->rowsArray, function($item) {
            return $item !== null;
        });
    }

    function setRow() {
        $this->rowsArray[$this->currentRowIndex] = null;
    }

    function hasNextRow() {
        return $this->currentRowIndex <= (count($this->rowsArray) - 1);
    }

    function rowCount() {
        return count($this->rowsArray);
    }

    function &getRow() {
        return $this->rowsArray[$this->currentRowIndex];
    }

    // BND TODO
    function __toString() {
        $data = [];
        foreach($this->rowsArray as $row) {
            foreach($this->headerArray as $header) {
                $data[] = $row[$header];
                $data[] = $this->delimiter;
            }
            $data[] = PHP_EOL;
        }
        return implode('', $data);
    }

    public function current() {
        return current($this->rowsArray);
    }

    public function next() {
        return next($this->rowsArray);
    }

    public function key() {
        return key($this->rowsArray);
    }

    public function valid() {
        $key = key($this->headerArray);
        return ($key !== null && $key !== false);
    }

    public function rewind() {
        rewind($this->rowsArray);
    }
}