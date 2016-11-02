<?php
/**
 * search logs from post
 *
 * so here is how this works... the search form is presented on
 *   content/search.php.  when a form is posted index.php
 *   sends the request to include/post_processor.php which
 *   recognizes it's a search and includes yours truly.
 *
 * this page simply builds a where clause and index displays
 *   content/search.php again.  this time since $where
 *   is set, the content/search.php includes content/list.php
 *   which then applies the where clause to what normally
 *   shows all records, and voila, we have a search.
 *
 */

function valuetodate($str) {
  $r = @date_parse($str);
  if (!is_array($r) || (count($r['errors']) > 0)) {
    return null;
  } else {
    return $r['year']
      . '-' . str_pad($r['month'], 2, '0', STR_PAD_LEFT)
      . '-' . str_pad($r['day'], 2, '0', STR_PAD_LEFT)
      . ' ' . str_pad($r['hour'], 2, '0', STR_PAD_LEFT)
      . ':' . str_pad($r['minute'], 2, '0', STR_PAD_LEFT)
      . ':' . str_pad($r['second'], 2, '0', STR_PAD_LEFT);
  }
}

# easy:
#  ["subject"]=> string(0) ""
#  ["keyword"]=> string(0) ""
#  ["user"]=> string(0) ""
#  ["type"]=> string(0) ""
#  ["jira"]=> string(0) ""
#  ["after"]=> string(0) ""
#  ["before"]=> string(0) ""
#  ["api"]=> string(1) "1"
#  ["outage"]=> string(1) "1"
#
# hard:
#  ["service"]=> string(0) ""
#  ["system"]=> string(0) ""
#  ["team"]=> string(0) ""

$j = array();
$s = array();
$where = '';

foreach($_POST as $field=>$value) {
  if (strlen($value) == 0) continue;
  $value = $dbh->real_escape_string($value);
  if ($field == 'search') continue;
  switch ($field) {
  case 'subject':
    $s[count($s)]="`log`.`subject` LIKE '%$value%'";
    break;
  case 'keyword':
    $s[count($s)]="`log`.`detail` LIKE '%$value%'";
    break;
  case 'user':
    message("search using field 'user' is not implemented", 'warn');
    #$s[count($s)]='';
    break;
  case 'type':
    $s[count($s)]="`log`.`type` LIKE '%$value%'";
    break;
  case 'jira':
    $s[count($s)]="`log`.`jira` LIKE '%$value%'";
    break;
  case 'after':
    $s[count($s)]="`log`.`posted` > '".valuetodate($value)."'";
    break;
  case 'before':
    $s[count($s)]="`log`.`posted` < '".valuetodate($value)."'";
    break;
  case 'api':
    # values: 1=include, 0=exclude, -1=only
    if ($value == 0) {
      $s[count($s)]="`log`.`api` = 0";
    } elseif ($value == -1) {
      $s[count($s)]="`log`.`api` = 1";
    }
    break;
  case 'outage':
    # values: 0=include, 1=exclude, -1=only
    if ($value == 0) {
      $s[count($s)]="`log`.`notification` = 0";
    } elseif ($value == -1) {
      $s[count($s)]="`log`.`notification` = 1";
    }
    break;
    break;
  case 'service':
    $j[count($j)]='`log_service_map` ON `log`.`id` = `log_service_map`.`log_id` AND `log`.`group` = `log_service_map`.`log_group`';
    $j[count($j)]='`service` ON `service`.`id` = `log_service_map`.`service_id`';
    $s[count($s)]="`service`.`name` LIKE '%$value%'";
    break;
  case 'system';
    $j[count($j)]='`log_system_map` ON `log`.`id` = `log_system_map`.`log_id` AND `log`.`group` = `log_system_map`.`log_group`';
    $j[count($j)]='`system` ON `system`.`id` = `log_system_map`.`system_id`';
    $s[count($s)]="`system`.`name` LIKE '%$value%'";
    break;
  case 'team':
    $j[count($j)]='`log_team_map` ON `log`.`id` = `log_team_map`.`log_id` AND `log`.`group` = `log_team_map`.`log_group`';
    $j[count($j)]='`team` ON `team`.`id` = `log_team_map`.`team_id`';
    $s[count($s)]="`team`.`name` LIKE '%$value%'";
    break;
  default:
    message('invalid search query', 'crit');
    break;
  }
}

if (count($j) > 0 ) $where = "LEFT JOIN " . implode(" LEFT JOIN ", $j) . ' ';
if (count($s) > 0 ) $where .= "WHERE " . implode(" AND ", $s);
if (strlen(trim($where))==0) { unset($where); }

?>
