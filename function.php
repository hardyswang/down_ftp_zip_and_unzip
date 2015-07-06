<?php
/**
 * write log
 */
function access_log($info1, $info2){
    if(!(defined('ENABLE_LOG') && ENABLE_LOG == 1)){
        return false;
    }
    $log_fd = fopen('/tmp/app_blacklistservice.log', "a+");
    if ($log_fd == false)
        return;
    $trace = debug_backtrace();
    $out = array();
    $ix = -1;
    foreach ($trace as $point) {
        if (empty($point['class']))
            $point['class'] = '';
        if (empty($point['type']))
            $point['type'] = '';

        $out[$ix] = strval($ix);
        $out[$ix] .= isset($point['file']) ? strval($point['file']) : '';
        $out[$ix] .= "(";
        $out[$ix] .= isset($point['line']) ? strval($point['line']) : '';
        $out[$ix] .= "): ";
        $out[$ix] .= isset($point['class']) ? strval($point['class']) : '';
        $out[$ix] .= isset($point['type']) ? strval($point['type']) : '';
        $out[$ix] .= isset($point['function']) ? strval($point['function']) : '';
        $out[$ix] .= "(";
        if (-1 == $ix)
            $location = 'location: [' . $point['file'] . ':' . $point['line'] . ']';
        if (count($trace) - 2 == $ix)
            $file_name = basename($point['file']);
        if (is_array($point['args']) && count($point['args']) > 0) {
            foreach ($point['args'] as $arg) {
                switch (gettype($arg)) {
                    case 'array':
                    case 'resource':
                        $out[$ix] .= gettype($arg);
                        break;
                    case 'object':
                        $out[$ix] .= get_class($arg);
                        break;
                    case 'string':
                        if (strlen($arg) > 30)
                            $arg = substr($arg, 0, 27) . ' ...';
                        $out[$ix] .= "'{$arg}'";
                        break;
                    default:
                        $out[$ix] .= $arg;
                }
                $out[$ix] .= ', ';
            }
            $out[$ix] = substr($out[$ix], 0, -2);
        }
        $out[$ix] .= ")\n";
        $ix++;
    }
    $out = array_reverse($out);
    array_pop($out);

    $out = array_reverse($out);
    array_pop($out);

    $log_info = "\n\n========================================  " . $file_name . "  =========================================\n\n";
    list($usec, $sec) = explode(" ", microtime());
    $log_info .= "[" . date("Y-m-d H:i:s", $sec) . substr($usec, 1) . "]";

    $log_info .= "\n" . $location . "\n";
    $log_info .= "\n" . $info1 . "\n" . print_r($info2, TRUE) . "\n";
    fwrite($log_fd, $log_info, strlen($log_info));
    fclose($log_fd);
}    
