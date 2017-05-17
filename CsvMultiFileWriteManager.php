<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (All rights reserved)
 * Description: A helper class for keeping track of state when spreading data across
 * separate, sequentially numbered files. This class will keep track of the count of rows and files
 * as well as generate a file name based on a prefix and suffix.
 */

class CsvMultiFileWriteManager extends MultiFileWriteManager
{
    private $headerRow = null;

    function getHeaderRow() {
        return $this->headerRow;
    }

    function setHeaderRow($headerRow) {
        $this->headerRow = $headerRow;
    }

    function newFileCallback() {
        parent::newFileCallback();
        if($this->headerRow === null) {
            throw new Exception('No header specified for CSV file: ' . $this->currentFileName());
        }
        $this->file()->addLineFromArray($this->headerRow);
    }
}