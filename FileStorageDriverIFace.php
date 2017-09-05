<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
interface FileStorageDriverIFace
{
    function open();
    function close();
    function fwrite($arr);
    function lineCount();
    function createFile();
    function fileExists();
}