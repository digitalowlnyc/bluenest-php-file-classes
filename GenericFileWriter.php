<?php

/**
 * A buffered file writer
 */
class GenericFileWriter
{
    /** @var FileStorageDriverIFace */
    protected $storageDriverInstance;
    protected $NL = "\r\n";
    private $fileName;
    private $dieIfFileExists = false;
    private $hasOutputStarted = false;

    private $buffer = array();
    private $bufferSizeInLines;

    /**
     * FileWriter constructor.
     */
    public function __construct($filename, $storageDriverInstance)
    {
        $this->storageDriverInstance = $storageDriverInstance;
        $this->storageDriverInstance->open(); // BND REVIEW

        if($this->dieIfFileExists && $this->storageDriverInstance->fileExists()) {
            die("File already exists: " . $filename);
        }

        $this->fileName = $filename;
    }

    public function setBufferSize($bufferSizeInLines) {
        $this->bufferSizeInLines = $bufferSizeInLines;
    }

    /**
     * If array, each component is a new line
     * @param $data
     */
    function add($data) {
        if(is_array($data)) {
            foreach($data as $line) {
                $this->internalAddLine($line);
            }
        } else {
            $this->internalAddLine($data);
        }
    }

    function addLineFromArray($lineArray) {
        foreach($lineArray as $lineElement) {
            $this->add($lineElement);
        }
    }

    function internalAddLine($data)
    {  // BND revisit performance
        if(is_string($data)) {
            $this->fwrite($data . $this->NL);
        } else {
            $this->fwrite($data);
        }
    }

    function commitBuffer() {
        echo 'Committing buffer of line size: ' . count($this->buffer) . PHP_EOL;
        $data = implode('', $this->buffer);
        $this->storageDriverInstance->fwrite($data);
        $this->buffer = array();
    }

    function close() {
        if(count($this->buffer)) {
            $this->commitBuffer();
        }
    }

    function getFileName() {
        return $this->fileName;
    }

    function getLineCount() {
        return $this->storageDriverInstance->lineCount();
    }

    function fputs($data) {
        $this->fwrite($data);
    }

    function fwrite($data) {
        $this->hasOutputStarted = true;
        if($this->bufferSizeInLines > 0) {
            $this->buffer[] = $data;

            if(count($this->buffer) >= $this->bufferSizeInLines) {
                $this->commitBuffer();
            }
        } else {
            $this->storageDriverInstance->fwrite($data);
        }
    }
}