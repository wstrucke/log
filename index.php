<?php
/**
 * main
 *
 */

include('include/functions.php');
include('include/sessions.php');
include('include/settings.php');

# auth check
$authenticated = false;

# auth user
$auth_user = '';

# default target
$defaultTarget = 'login';

# no template
$noTemplate = array('api');

# enable output by default
$output = true;

# valid targets
$validTargets = array('api', 'help', 'home', 'list', 'login', 'logout', 'post', 'report', 'search', 'view');

# application version
$versionID = '2015-10-13';
$versionNumber = '0.1';

# attempt to connect to the database
$dbh = @new mysqli($settings['dbhost'], $settings['dbuser'], $settings['dbpass'], $settings['dbname']);

if ($dbh->connect_errno) {
  message("Error: failed to connect to database!", 'crit');
} else {
  $r = $dbh->query("SELECT * FROM `settings`");
  if (is_object($r) === false) { die('Unable to connect to database'); }
  while ($row = $r->fetch_array(MYSQLI_ASSOC)) {
    if (! db_qcheck($row, true)) continue;
    $settings[$row['name']] = $row['value'];
  }
}

# build url
if ($settings['ssl'] == '1') {
  $base_url = 'https://'.$settings['domain'];
} else {
  $base_url = 'http://'.$settings['domain'];
}

# load target
if (count($_REQUEST) > 0 && isset($_REQUEST['t'])) {
  $target = explode('/', $_REQUEST['t']);
}

# handle logout
if ($target[0] == 'logout') {
  terminate_session();
  exit;
}

# check for no template
if (in_array($target[0], $noTemplate)) { $output = false; }

# set timezone
date_default_timezone_set($settings['timezone']);

# set encoding
if ($output) { header('Content-Type: text/html; charset=utf8'); }

# start session
start_session();

# check for a posted form
if (count($_POST) > 0) {
  include('include/post_processor.php');
}

if ($output) { include('content/header.php'); }

# conditionally output navigation
if ($authenticated === true) {
  if ($output) { include('content/nav.php'); }
  $defaultTarget='home';
} else {
  if ($settings['ssl'] == '1') {
    message('LDAP Authentication is not enabled', 'info');
  } else {
    message('LDAP Authentication is not enabled and this connection is NOT encrypted', 'warn');
  }
  $target = array('login');
}

# output any queued messages
if ($output) { message_callback(); }

# aliases
if ($target[0] == 'browse') { $target[0] = 'list'; }

if (in_array($target[0], $validTargets) === false) {
  include('content/'.$defaultTarget.'.php');
} else {
  include('content/'.$target[0].'.php');
}

if ($output) {
  # output any queued messages
  message_callback();

  include('content/footer.php');
}

?>
