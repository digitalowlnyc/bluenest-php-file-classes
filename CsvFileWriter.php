<?php

require_once 'FileWriter.php';

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (All rights reserved)
 */

define("EMPTY_QUOTE_CHARACTER", "");

class CsvFileWriter extends FileWriter
{
    private $delimiter;
    private $colCount = null;
    private $headerRow = null;
    private $cellLengthLimit = null;
    private $excelMode = false;
    private $quoteValueCharacter = EMPTY_QUOTE_CHARACTER;

    const EXCEL_CELL_LENGTH_LIMIT = 32767;
    const CELL_LENGTH_LIMIT_TEXT_SUFFIX = "... TRUNCATED ...";

    function __construct($filename, $delimiter = ",") {
        $this->delimiter = $delimiter;
        parent::__construct($filename);
    }

    /**
     * @param $lineArray array Takes values from array
     * @throws Exception
     */
    function addLineFromArray($lineArray) {
        if($this->colCount === null) {
            $this->colCount = count($lineArray);
            $this->headerRow = $lineArray;
        } else {
            if(count($lineArray) !== $this->colCount) {
                throw new Exception('Column counts do not match, current row = ' . count($lineArray) . ' vs ' . $this->colCount);
            }
        }
        $this->writeCsvLine($this->fileHandle, $lineArray, $this->colCount);
        $this->lineCount += 1;
    }

    function internalAddLine($data) {
        throw new Exception('This method not supported for CSV files');
    }

    function excelModeOn() {
        $this->excelMode = true;
        $this->cellLengthLimit = self::EXCEL_CELL_LENGTH_LIMIT;
    }

    private function writeCsvLine($fp, $lineArray, $colCount, $cellLengthLimit = null) {
        if($this->cellLengthLimit !== null) {
            $this->cellLengthLimit = $cellLengthLimit;
        }

        $escapedValues = array();
        $colCounter = 0;

        foreach($lineArray as $value) {
            $colCounter += 1;
            $colIndex = $colCounter - 1;

            if(!is_string($value) && !is_numeric($value)) {
                throw new \Exception(sprintf("CsvFileWriter: Value for column '%s' is not a string: '%s'", $this->headerRow[$colIndex], debugVar($value)));
            }

            if($this->excelMode) {
                // BND TODO
                $value = str_replace('"', '""', $value); // escape double quotes
            }

            if($cellLengthLimit !== null && strlen($value) > $cellLengthLimit) {
                $suffixText = self::CELL_LENGTH_LIMIT_TEXT_SUFFIX;
                trigger_error('CSV column #' . $colCounter . ' was truncated due to cell size limit setting of ' . $cellLengthLimit . ' characters', E_USER_WARNING);

                // Figure out how much of original string to cut off so we can add the 'truncated' identifier to the cell content
                $truncatedTextAllowedLength = $cellLengthLimit - strlen($suffixText);

                $cappedStringText = substr($value, 0, $truncatedTextAllowedLength);
                $cappedStringText .= $suffixText;

                if($cappedStringText > $cellLengthLimit) {
                    throw new Exception('Capped text is larger than cell limit');
                }
                $value = $cappedStringText;
            }

            $escapedValues[] = $value;
        }

        $line = $escapedValues;

        $literalLineArray = [];
        foreach($line as &$val) {

            if(stringContains($val, $this->delimiter)) {
                // first, escape any quotes
                if(stringContains($val, '"')) {
                    $val = str_replace('"', '""', $val); // BND FIXME
                }

                $val = "\"" . $val . "\""; // BND FIXME
            }
            if($this->quoteValueCharacter !== EMPTY_QUOTE_CHARACTER) {
                $literalLineArray[] = $this->quoteValueCharacter . $val . $this->quoteValueCharacter;
            } else {
                $literalLineArray[] = $val;
            }
        }

        $this->fputs(implode($this->delimiter, $literalLineArray) . $this->NL);
    }
}