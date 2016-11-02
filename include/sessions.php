<?php
/**
 * session handling
 *
 */

$auth_explicit = true;
$session_mode = 'database';
$session_qualifier = 'PHPSESSID';
$session_timeout = 28800;

function start_session() {
  global $session_qualifier, $session_timeout, $authenticated, $auth_user;

  # Generating cookies must take place before any HTML.
  # Check for existing "SessionId" cookie
  if (array_key_exists($session_qualifier, $_COOKIE)) {
    $id = $_COOKIE[$session_qualifier];
  } else {
    $id = '';
  }

  if ($id == '') {
    # generate a random number to randomize the session_ids
    srand ((double) microtime( )*100000000);
    $rand1 = (rand()%1000);
    for ($counter=0;$counter<100;$counter++) { $random[$counter]=$rand1 * $counter; }
    $rand1 = (rand()%100);
    # Use user's IP address to make more unique.
    $random_string = (intval(md5('lets go bucks!')) * $random[12]) . $random[44] . 'f' . $random[0] . 'u';
    $random_string .= $random[10] . 'd' . $random[13] . 'ge' . $random[20] . 'm' . $random[33];
    $random_string .= bin2hex('*') . $random[12] . 'c' . $random[18] . 'h' . $random[86] . bin2hex('*');
    $random_string .= $random[88] . 'g' . $random[99] . 'a' . $random[10] . 'n' . $random[7];
    $random_string .= (intval(bin2hex($_SERVER['REMOTE_ADDR'])) * $random[37]) . ($random[76] % 5);
    $random_string .= $random[69] . ($random[24] * $random[56]);
    if (strlen($random_string) > 110) { $random_string = left($random_string, 110); }
    $id = uniqid($random_string);
  }

  # temporarily define static values
  $httponly = false;

  # initialize the session parameters
  session_id($id);
  session_name($session_qualifier);

  # set site expiration time
  @ini_set('session.gc_maxlifetime', (string)$session_timeout);
  session_cache_limiter('private'); // set to private_no_expire for non dynamic content
  session_cache_expire($session_timeout / 60);

  # disallow php version header
  if (function_exists('header_remove')) @header_remove('X-Powered-By');

  # start the session
  if (@session_start() === false) { message('UNABLE TO INIT SESSION!', 'crit'); }
  @header("Cache-control: private");

  if (array_key_exists('active', $_SESSION) && ($_SESSION['active'])) {
    $authenticated = true;
    $auth_user = $_SESSION['auth_uid'];
  }
}

function close_session() {
  return true;
}

function destroy_session($id) {
  global $dbh;
  $id = $dbh->real_escape_string($id);
  $dbh->query("DELETE FROM session WHERE id='$id'");
  return true;
}

function gc_session() {
  global $dbh;
  $time = time();
  $dbh->query("DELETE FROM session WHERE expires < $time");
  return true;
}

function open_session() {
  return true;
}

function read_session($id) {
  global $dbh, $session_timeout;

  # sanitize input
  $id = $dbh->real_escape_string($id);

  # initialize result
  $data = '';

  # fetch session data from the selected database
  $time = time();

  $result = $dbh->query("SELECT `data` FROM `session` WHERE `id`='$id' AND `expires` > '$time'");

  if (is_object($result) === false) { return false; }

  $row = $result->fetch_array(MYSQLI_ASSOC);
  if (db_qcheck($row)) {
    # get the session data
    $data = $row['data'];
    # automatically update the expiration
    $time = time() + $session_timeout;
    $dbh->query("UPDATE `session` SET `expires`='$time' WHERE `id`='$id'");
  }

  return $data;
}

function terminate_session() {
  global $session_qualifier, $session_timeout, $settings, $authenticated;
  # grab session id before removing the cookie
  $session = $_COOKIE[$session_qualifier];
  # Kill the Cookie by setting its expiration in the past
  @header("Set-Cookie: $session_qualifier=; Max-Age=-$session_timeout; Domain=$domain; Path=/; secure;");
  unset($_COOKIE[$session_qualifier]);
  # now we have to start the session in order to terminate it on the server
  session_id($session);
  @header("Cache-control: private");
  destroy_session($session);
  # redirect the client home
  if ( $settings['ssl'] ) {
    echo "<html><head><meta http-equiv=\"refresh\" content=\"0;url=https://".$settings['domain']."\"></head><body><h1>Logged out</h1></body></html>\n";
  } else {
    echo "<html><head><meta http-equiv=\"refresh\" content=\"0;url=http://".$settings['domain']."\"></head><body><h1>Logged out</h1></body></html>\n";
  }
  $authenticated = false;
}

function write_session($id, $data) {
  global $dbh, $settings, $session_timeout;
  if (! is_object($dbh)) {
    $dbh = @new mysqli($settings['dbhost'], $settings['dbuser'], $settings['dbpass'], $settings['dbname']);
    if (! is_object($dbh)) { die('unable to connect to database'); }
  }

  # conditionally update the session data
  if (strlen($data) > 0) {
    # sanitize input
    $id = $dbh->real_escape_string($id);
    $data = $dbh->real_escape_string($data);
    # set expiry time
    $expiry = time() + $session_timeout;
    # perform operation
    $dbh->autocommit(false);
    $dbh->query("DELETE FROM `session` WHERE `id`='".$id."'");
    $dbh->query("INSERT INTO `session` (`id`, `data`, `expires`) VALUES ('$id', '$data', '$expiry')");
    $dbh->commit();
  }

  return true;
}


# configure session handler
if ($session_mode == 'database') {
  # register this object as the session handler
  session_set_save_handler(
    "open_session",
    "close_session",
    "read_session",
    "write_session",
    "destroy_session",
    "gc_session"
  );
}
