<?php

require_once 'FileWriter.php';
require_once 'CsvFileWriter.php';

if(!defined('NL')) {
    define('NL', PHP_EOL);
}

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (All rights reserved)
 * Description: A helper class for keeping track of state when spreading data across
 * separate, sequentially numbered files. This class will keep track of the count of rows and files
 * as well as generate a file name based on a prefix and suffix.
 */
class MultiFileWriteManager
{
    private $fileCounter = 0;
    private $currentFileRowCounter = 0;
    private $currentFileName = null;
    private $rowsPerFile = 1;
    private $filePrefix = 'output';
    private $fileSuffix = '.log';
    /** @var string */
    private $fileWriterClass = null;
    /** @var FileWriter */
    private $fileWriterInstance = null;
    private $verbose = true;

    function __construct($rowsPerFile, $filePrefix, $fileSuffix, $fileWriterClass = FileWriter::class) {
        $this->rowsPerFile = $rowsPerFile;
        $this->filePrefix = $filePrefix;
        $this->fileSuffix = $fileSuffix;
        $this->fileWriterClass = $fileWriterClass;

        $this->moveToNextFile();
    }

    function moveToNextFile() {
        $this->fileCounter += 1;
        $this->currentFileName = $this->filePrefix . $this->fileCounter . $this->fileSuffix;
        $this->currentFileRowCounter = 0;
    }

    function incrementRow() {
        $this->currentFileRowCounter += 1;
        if($this->rowsPerFile <= $this->currentFileRowCounter) {
            $this->moveToNextFile();
            $this->createFileWriterInstance();
        }
    }

    function createFileWriterInstance() {
        $fileWriterClass = $this->fileWriterClass;
        $this->fileWriterInstance = new $fileWriterClass($this->currentFileName);
        $this->newFileCallback();
    }

    function currentFileName() {
        return $this->currentFileName;
    }

    /**
     * @return FileWriter
     */
    function file() {
        // Wait until we actually try to access to file the first to before creating it
        if($this->fileWriterInstance === null) {
            $this->createFileWriterInstance();
        }
        return $this->fileWriterInstance;
    }

    function newFileCallback() {
        if($this->verbose) {
            echo get_class($this) . ': New file: ' . $this->currentFileName() . NL;
        }
    }

    function close() {
        if($this->fileWriterInstance !== null) {
            $this->fileWriterInstance->close();
        }
    }
}