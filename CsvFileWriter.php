<?php

require_once 'FileWriter.php';

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (All rights reserved)
 */
class CsvFileWriter extends FileWriter
{
    private $colCount = null;
    private $headerRow = null;
    private $cellLengthLimit = 32767;

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
        fputs($this->fileHandle, $this->NL);
        $this->lineCount += 1;
    }

    function internalAddLine($data) {
        throw new Exception('This method not supported for CSV files');
    }

    private function writeCsvLine($fp, $lineArray, $colCount, $cellLengthLimit = null) {
        if($cellLengthLimit === null) {
            $cellLengthLimit = $this->cellLengthLimit;
        }

        $cellLengthLimitText = "... TRUNCATED ...";

        $escapedValues = array();
        $colCounter = 0;

        foreach($lineArray as $value) {
            $colCounter += 1;
            $colIndex = $colCounter - 1;
            try {
                $value = str_replace('"', '""', $value);
            } catch(Exception $e) {
                echo 'Column with problem: ' . $this->headerRow[$colIndex];
                die();
            }
            //$value = str_replace(':', ' ', $value);

            if(strlen($value) > $cellLengthLimit) {
                trigger_error('CSV column #' . $colCounter . ' was truncated due to cell size limit setting of ' . $cellLengthLimit . ' characters', E_USER_WARNING);

                // Figure out how much of original string to cut off so we can add the 'truncated' identifier to the cell content
                $truncatedTextAllowedLength = $cellLengthLimit - strlen($cellLengthLimitText);

                $cappedStringText = substr($value, 0, $truncatedTextAllowedLength);
                $cappedStringText .= $cellLengthLimitText;

                if($cappedStringText > $cellLengthLimit) {
                    throw new Exception('Capped text is larger than cell limit');
                }
                $value = $cappedStringText;
            }
            /*
            $value = str_replace(',', ' ', $value);
            $value = str_replace("\n", ' ', $value);
            $value = str_replace("\r", ' ', $value);
            $value = str_replace("=", ' ', $value);
            $value = str_replace("<", ' ', $value);
            $value = str_replace(">", ' ', $value);
            $value = str_replace("\\", ' ', $value);
            $value = str_replace("/", ' ', $value);
    */
            $escapedValues[] = $value;
        }

        $line = $escapedValues;

        $i = 0;
        foreach($line as $val) {
            $i += 1;
            $val = '"' . $val . '"';
            fputs($fp, $val);

            if($i !== $colCount) {
                fputs($fp, ",");
            }
        }
    }
}