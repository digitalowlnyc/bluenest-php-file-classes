<?php

define('EMPTY_STRING', '');

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class CsvRow implements ArrayAccess, Iterator
{
    protected $rowValues; // holds key => $val for any columns that are not empty. Value may be array which includes metadata, or actual value.
    protected $headerArray;

    function __construct($data, &$headerArray) {
        $this->rowValues = arrayValue($data);
        $this->headerArray = arrayValue($headerArray);
    }

    public function &getDataRef() {
        return $this->rowValues;
    }

    public function getColOrThrow($key) {
        if(!$this->columnValueExists($key)) {
            throw new \Exception("Row is missing column: " . $key);
        }
        return $this->offsetGet($key);
    }

    public function getCol($key) {
        return $this->offsetGet($key);
    }

    public function setCol($key, $val) {
        $this->offsetSet($key, $val);
    }

    public function &getColRef($key) {
        if(!array_key_exists($key, $this->rowValues)) {
            return EMPTY_STRING;
        }
        return $this->rowValues[$key];
    }
/*
    function __get($name)
    {
        if(!array_key_exists($name, $this->rowValues)) {
            return EMPTY_STRING;
        }

        return $this->rowValues[$name];
    }
*/
    public function offsetSet($offset, $value) {
        $this->rowValues[$offset] = $value;
    }

    /**
     * This actually checks if the column is present in the CSV file, not if this row has a value
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->headerArray);
    }

    public function columnValueExists($offset) {
        return array_key_exists($offset, $this->rowValues);
    }

    public function offsetUnset($offset) {
        unset($this->rowValues[$offset]);
    }

    /**
     * @param mixed $key
     * @return object
     */
    public function offsetGet($key) {
        if(!is_int($key) && !is_string($key)) {
            throw new Exception("Unexpected key type: " . debugVar($key));
        }
        if(!array_key_exists($key, $this->rowValues)) {
            return EMPTY_STRING;
        }

        return $this->rowValues[$key];
    }

    public function rewind() {
        reset($this->headerArray);
    }

    public function key() {
        $key = key($this->headerArray);
        return $key;
    }

    public function current() {
        $currentColumn = current($this->headerArray);
        $current = $this->offsetGet($currentColumn);
        if($current === false) {
            return false;
        }
        return $current;
    }

    /**
     * Return map of $col=>$value for any non-empty columns
     *
     * @return array
     */
    function values() {
        $valueMap = [];
        foreach($this->rowValues as $key => $item) {
            $valueMap[$key] = $this->offsetGet($key);
        }
        return $valueMap;
    }

    public function next() {
        // BND TODO: Handle calls to unset when iterating
        /*
         *
         * echo 'Next()' . PHP_EOL;

        if($this->currentLoopItem !== null && $this->currentLoopItem !== each($this->rowValues)[0]) {
            echo 'Wrong place' . $this->currentLoopItem . ' vs ' .  each($this->rowValues)[0];
            $item = current($this->rowValues);
            $this->currentLoopItem = each($this->rowValues)[0];
        } else {
            echo 'Correct place' . $this->currentLoopItem . ' vs ' .  each($this->rowValues)[0];
            $item = next($this->rowValues);
            $this->currentLoopItem = each($this->rowValues)[0];
        }*/
        $nextColumn = next($this->headerArray);
        if($nextColumn === false) {
            return false;
        }
        return $this->offsetGet($nextColumn);
    }

    public function valid() {
        $key = key($this->headerArray);
        return ($key !== null && $key !== false);
    }

    public function toArray() {
        $vals = [];
        foreach($this->headerArray as $header) {
            $vals[$header] = $this->offsetGet($header);
        }

        return $vals;
    }

    public function __toString() {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}