<?php

/**
 * A buffered file reader
 */
class FileReader
{
    private $fileHandle;
    private $lineBufferCapacity = 100;
    private $lineBuffer = "";
    private $doneReading = false; // have we reached end of file
    private $currentLine = 0;
    private $lineBufferSizeCounter = 0;
    /**
     * FileWriter constructor.
     */
    public function __construct($filename, $lineBufferCapacity)
    {
        if(!file_exists($filename))
            die("File does not exist: " . $filename);

        $this->fileHandle = fopen($filename, 'r');

        if($this->fileHandle === false)
            die("Could not open file: " . $filename);

        $this->lineBufferCapacity = $lineBufferCapacity;
    }

    function isDoneReading() {
        return $this->doneReading;
    }

    function getData() {

        while($this->lineBufferSizeCounter < $this->lineBufferCapacity) {
            $this->lineBufferSizeCounter+=1;
            $nextLine = fgets($this->fileHandle);
            $this->currentLine += 1;
            if($nextLine === false) {
                $this->doneReading = true;
                break;
            }

            $this->lineBuffer .= $nextLine . "\n";
        }

        $data = $this->lineBuffer;
        $this->lineBuffer = "";
        $this->lineBufferSizeCounter = 0;
        return $data;
    }

    function done() {
        fclose($this->fileHandle);
    }
}