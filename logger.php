<?php
function log_action($action) {
    $log_file = "log.txt";
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "$timestamp - $action\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
?>
