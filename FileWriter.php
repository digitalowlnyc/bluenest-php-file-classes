<?php 

/**
 * A buffered file writer
 */
class FileWriter
{
    protected $fileHandle;
    protected $lineCount = 0;
    protected $NL = "\r\n";
    private $fileName;
    private $dieIfFileExists = false;
    private $hasOutputStarted = false;

    private $buffer = array();
    private $bufferSizeInLines;

    /**
     * FileWriter constructor.
     */
    public function __construct($filename)
    {
        if($this->dieIfFileExists && file_exists($filename)) {
            die("File already exists: " . $filename);
        }

        $directory = pathinfo($filename, PATHINFO_DIRNAME);

        if(!is_dir($directory)) {
            throw new Exception("'" . $directory . "' is not a valid directory for writing, tried to write file: " . $filename);
        }

        $this->fileHandle = fopen($filename, 'w');

        $this->fileName = $filename;

        if($this->fileHandle === false) {
            $this->fileHandle = null;
            die("Could not open file: " . $filename);
        }
    }

    public function setBufferSize($bufferSizeInLines) {
        $this->bufferSizeInLines = $bufferSizeInLines;
    }

    /**
     * If array, each component is a new line
     * @param $data
     */
    function add($data) {
        if(gettype($data) === "array") {
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

    function internalAddLine($data) {
        $this->fwrite($data . $this->NL);
    }

    function commitBuffer() {
        //echo 'Committing buffer of size: ' . count($this->buffer) . PHP_EOL;
        $data = implode('', $this->buffer);
        fwrite($this->fileHandle, $data);
        $this->buffer = array();
    }

    function close() {
        if(count($this->buffer)) {
            $this->commitBuffer();
        }
        fclose($this->fileHandle);
    }

    function getFileName() {
        return $this->fileName;
    }

    function getLineCount() {
        return $this->lineCount;
    }

    function getFileSize() {
        return filesize($this->fileName);
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
            fwrite($this->fileHandle, $data);
        }
    }

    function writeBOM() {
        if($this->hasOutputStarted) {
            throw new \Exception('BOM must be at start of file, data already written to file');
        }
        fputs($this->fileHandle, "\xEF\xBB\xBF"); // UTF-8
    }
}