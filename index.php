<?php
include('config.php');

if (empty($_REQUEST['uid']))
    display_xml(0);

$current_path = dirname(__FILE__);
$extract_path = $current_path .'/upload/';

if(!file_exists($extract_path .'blacklist.txt')){
    display_xml(0);
}

$result = resolve_file($extract_path .'blacklist.txt');

display_xml($result);


function resolve_file($file) {
    $result = 0;
    if (!(file_exists($file) && is_readable($file)))
        return $result;

    $fp = fopen($file, 'r');
    while (!feof($fp)) {
        $black = trim(fgets($fp, 4096));
        if (strcasecmp($_REQUEST['uid'],$black) == 0) {
            $result = 1;
            break;
        }
    }
    fclose($fp);

    return $result;
}


function display_xml($result = 0) {
    header('Content-Type: text/xml;');
    $str = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
    $str .= "<root>";
    $str .= "<result>$result</result>";
    $str .= "</root>";
    echo $str;
    exit;
}


