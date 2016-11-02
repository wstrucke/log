<?php
/* API interface
 *
 */

include('include/log-1.php');

array_shift($target);
$log = new log($dbh);


switch ($target[0]) {
  case 'get':
    $record = $log->get($target[2], $target[1]);
    if (is_array($record)) {
      echo json_encode($record);
    } else {
      http_response_code(404);
    }
    break;
  default:
    http_response_code(400);
}

?>
