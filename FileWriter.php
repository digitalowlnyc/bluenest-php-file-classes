<?php 

/**
 * A buffered file writer
 */
class FileWriter
{
    protected $fileHandle;
    protected $lineCount = 0;
    protected $NL = PHP_EOL;
    private $fileName;
    private $dieIfFileExists = false;

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
        fwrite($this->fileHandle, $data . $this->NL);
    }

    function close() {
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
}