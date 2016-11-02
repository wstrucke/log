<?php
/**
 * common functions
 *
 */

$msgQueue = array();

function db_qcheck(&$result, $more = false)
/* given a result from a database query, return true if there was exactly one result
 *
 * if $more == true, return true if there was one or more results
 *
 * returns false if there was an error or zero results
 *
 */
{
  if ($result === false) return false;
  if ($more && (count($result) > 0)) return true;
  if (count($result) == 1) return true;
  return false;
}

function expand_parameter($str) {
  $set = array();
  # {a..b}
  if (preg_match('/{([0-9]+)..([0-9]+)}/', $str, $match)) {
    for($i=intval($match[1]);$i<=intval($match[2]);$i++){ $set[count($set)]=strval($i); }; $str = '';
  }
  # {a,b}
  if (preg_match('/{([^,}]+),([^,}]+)(,|})/', $str)) {
    while (preg_match('/{([^,}]+)(,|})/', $str, $match)) {
      $pos = strpos($str, $match[0]);
      $len = strlen($match[0]);
      $str = substr_replace($str, "{", $pos, $len);
      $set[count($set)]=$match[1];
    }
  }
  if (count($set)==0) return false;
  return $set;
}

function expand_parameters($str)
/* expand string like bash does
 *
 * examples:
 *   a{1,2} => a1 a2
 *   a{1..4} => a1 a2 a3 a4
 *
 */
{
  $search = '/{[^{}]+}/';
  $loop_counter = 0; $loop = 500;
  while (preg_match($search, $str, $match)) {
    preg_match_all('/(^| )([^, ]*'.str_replace(',', '\,', $match[0]).'[^ ]*)(,| |$)/', $str, $word);

    $master_pos = strpos($str, $word[2][0]);
    $master_len = strlen($word[2][0]);

    $set = expand_parameter($match[0]);
    if ($set === false) return false;

    $pos = strpos($word[2][0], $match[0]);
    $len = strlen($match[0]);
    $new_str = '';

    for($i=0;$i<count($set);$i++){
      $new_str .= substr_replace($word[2][0], $set[$i], $pos, $len) . ' ';
    }
    $new_str = substr($new_str, 0, strlen($new_str)-1);

    $str = substr_replace($str, $new_str, $master_pos, $master_len);
    $loop_counter++; if ($loop_counter >= $loop) { die('error in parameter expansion: loop detected'); }
  }
  return $str;
}

function l($link_text, $path='', $title = false)
/* create a link and return the string
 *
 */
{
  if (strpos($path, '://') === false) { $path = "/$path"; }
  if ($title === false ) {
    $str = "<a href=\"$path\">$link_text</a>";
  } else {
    $str = "<a href=\"$path\" title=\"$title\">$link_text</a>";
  }
  return $str;
}

function message($str, $severity = "info") {
  global $msgQueue;
  if (array_search($severity, array('info', 'ok', 'warn', 'crit')) === false) { $severity = 'info'; }
  $msgQueue[count($msgQueue)]=array($severity, $str);
}

function message_callback() {
  global $msgQueue;
  message_compressQueue();
  while($arr = array_shift($msgQueue)) {
    printf('  <div class="%s message">%s</div>'."\n", $arr[0], $arr[1]);
  }
}

function message_compressQueue() {
  global $msgQueue;
  $tmpArray = array('info'=>'', 'warn'=>'', 'crit'=>'', 'ok'=>'');
  while($arr = array_shift($msgQueue)) { $tmpArray[$arr[0]] .= $arr[1].' '; }
  foreach ($tmpArray as $severity=>$message) {
    if (strlen($message)>0) { $msgQueue[count($msgQueue)]=array($severity, trim($message)); }
  }
}

/* source: http://pageconfig.com/post/remove-undesired-characters-with-trim_all-php */
function trim_all($str, $what = NULL, $with = ' ') {
  if( $what === NULL ) { $what = "\\x00-\\x20"; }
  return trim( preg_replace( "/[".$what."]+/" , $with , $str ) , $what );
}

?>
