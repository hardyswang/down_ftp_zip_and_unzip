#! /usr/bin/php
<?php
include('config.php');
include('function.php');
include('ftp.class.php');
include('pclzip.lib.php');

$result = get_ftp_content(FTP_FILE, STORAGE_TARGET);

if($result === false || !file_exists(STORAGE_TARGET)){
    access_log('log', 'Download FTP file FAIL.');
    exit();
}

access_log('log', 'Download FTP file OK.');



$current_path = dirname(__FILE__);
$extract_path = $current_path .'/upload/';

if(!extract_zip(STORAGE_TARGET, $extract_path)){
    access_log('log', 'ZIP extract fail.');
    exit();
}

if(!file_exists($extract_path)){
    access_log('log', 'No file.');    
    exit();    
}
access_log('log', 'Obtain file.');   

function extract_zip($target, $extract_path) {
    $zip = new PclZip($target);

    if ($zip->extract(PCLZIP_OPT_PATH, $extract_path) == 0) {
        return false;
    } else {
        return true;
    }
}

function get_ftp_content($source, $target) {
    $result = false;

    $config = array(
        'hostname' => FTP_HOST,
        'username' => FTP_USER,
        'password' => FTP_PASSWORD,
        'port' => FTP_PORT,
        'timeout' => FTP_TIMEOUT
    );

    $ftp = new Ftp();
    $ftp->connect($config);
    if($ftp->existsFile($source)){
        $result = $ftp->download($source, $target);
    }else{
        access_log('log', 'No documents.');
        //clear_file();
    }

    $ftp->close();
    return $result;
}

function clear_file(){
    if(file_exists(STORAGE_TARGET)){
        $log = (unlink(STORAGE_TARGET)) ?'ZIP delete SUCCESS.' : 'ZIP delete FAIL.';
        access_log('log', $log);
    }
    $current_path = dirname(__FILE__);
    $extract_file = $current_path .'/upload/test.txt';
    if(file_exists($extract_file)){
        $log = (unlink($extract_file)) ?'TXT delete SUCCESS.' : 'TXT delete FAIL.';
        access_log('log', $log);
    }
    
}


