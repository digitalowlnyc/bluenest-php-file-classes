<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class FileStorageDriver implements FileStorageDriverIFace {
    private $filename;
    private $fileHandle;
    private $hasOutputStarted = false;

    function __construct($filename) {
        $this->filename = $filename;
    }

    function open() {
        $filename = $this->filename;

        $directory = pathinfo($filename, PATHINFO_DIRNAME);

        if(!is_dir($directory)) {
            throw new Exception("'" . $directory . "' is not a valid directory for writing, tried to write file: " . $filename);
        }

        $this->fileHandle = fopen($filename, 'w');

        if($this->fileHandle === false) {
            $this->fileHandle = null;
            die("Could not open file: " . $filename);
        }
    }

    function close() {
        fclose($this->fileHandle);
    }

    function fwrite($arr) {
        fwrite($this->fileHandle, $arr); // BND TODO array vs string
    }

    function fileExists() {
        return file_exists($this->filename);
    }

    function lineCount()
    {
        // TODO: Implement lineCount() method.
    }

    function writeBOM() {
        if($this->hasOutputStarted) {
            throw new \Exception('BOM must be at start of file, data already written to file');
        }
        fputs($this->fileHandle, "\xEF\xBB\xBF"); // UTF-8
    }

    function getFileSize() {
        return filesize($this->fileName);
    }

    function createFile() {
        // BND TODO: Implement createFile() method.
    }
}