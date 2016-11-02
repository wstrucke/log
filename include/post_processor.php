<?php
/**
 * form handler
 *
 * called and passed back from index
 *
 * requires:
 *   $_POST (array)
 *
 * returns:
 *   $target (array)
 *
 */

# login handler
if (isset($_POST['submit']) && $_POST['submit'] == 'Login') {
  if ($_POST['name'] == 'guest' && $_POST['password'] == 'guest') {
    $auth_user='guest';
    $authenticated=true;
    $_SESSION['active'] = true;
    $_SESSION['auth_uid'] = $auth_user;
    @session_write_close();
  }
}

if ($authenticated && isset($_POST['version'])) {
  $process_form = true;

  switch ($_POST['version']) {
    case '1': include_once('include/log-1.php'); break;
    default: $process_form = false; break;
  }

  if ($process_form) {
    $log = new log($dbh);
    $id = $log->post($_POST);
    if ($id !== false) { $target = array('view', 'WL', $id); }
  }
}

if ($authenticated && isset($_POST['search'])) {
  include('include/search.php');
}

?>
