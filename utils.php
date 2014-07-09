<?php

function set_eTagHeaders($file, $timestamp) {
    $gmt_mTime = gmdate('r', $timestamp);
 
    header('Cache-Control: public');
    header('ETag: "' . md5($timestamp . $file) . '"');
    header('Last-Modified: ' . $gmt_mTime);
 
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mTime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp . $file)) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }
}

?>
