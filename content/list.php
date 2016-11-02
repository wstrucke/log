<?php
/**
 * list log records
 *
 */

function nav_links($align) {
  global $iterator, $range_low, $page, $total_records;

  echo "  <ul class=\"$align navigation\">\n";
  if ($align == 'right') {
    $order = array(4,3,2,1);
  } else {
    $order = array(1,2,3,4);
  }
  foreach($order as $n) {
    $linktitle = false;
    switch ($n) {
    case 1:
      $innertext = '&#124;&lt;&lt;';
      $num = 0;
      if ($range_low <= 0) break;
      $linktitle = 'First';
      break;
    case 2:
      $innertext = "&lt;";
      if ($range_low <= 0) break;
      $num = ($range_low-$iterator);
      if ($num <= 0) $num = 0;
      $linktitle = 'Previous';
      break;
    case 3:
      $innertext = "&gt;";
      $num = ($range_low+$iterator);
      if ($num > ($total_records-$iterator)) $num = ($total_records-$iterator);
      if (($range_low == $num)||($num < 0)) break;
      $linktitle = 'Next';
      break;
    case 4:
      $innertext = "&gt;&gt;&#124;";
      $num = ($total_records-$iterator);
      if (($range_low == $num)||($num < 0)) break;
      $linktitle = 'Last';
      break;
    }
    if ($linktitle === false) {
      $text = "<li><span>$innertext</span></li>";
    } elseif ($num == 0) {
      $text = '<li>' . l($innertext, $page, $linktitle) . '</li>';
    } else {
      $text = '<li>' . l($innertext, "$page/$num", $linktitle) . '</li>';
    }
    echo "    $text\n";
  }
  echo "  </ul>\n";
}

# defaults
$detailLimit = 300;
$iterator = 15;
$page = "browse";
$range_low = 0;
$sql = "SELECT COUNT(*) FROM `log` $where";
$serviceLimit = 50;
$subjectLimit = 255;
$systemLimit = 120;

# get the total number of records
$result = $dbh->query($sql);
$r = @$result->fetch_array();
$total_records = $r[0];

# check args
if (count($target) > 1) {
  if (count($target) == 2) {
    $range_low = intval($target[1]);
  } else {
    $range_low = intval($target[1]);
    $iterator = intval($target[2]);
  }
}

$sql = "SELECT `log`.`group`, `log`.`id`, `log`.`posted`, `log`.`subject`, LEFT(`log`.`detail`, 1000) AS detail, `log`.`notification` FROM `log` $where ORDER BY `log`.`id` DESC LIMIT $range_low, $iterator";
$result = $dbh->query($sql);

$list_count = ($range_low+$iterator);
if ($list_count > $total_records) $list_count = $total_records;

?>
  <h3>Showing entries <?php echo ($range_low+1); ?> through <?php echo $list_count; ?>...</h3>

<?php nav_links('right'); ?>

  <ul class="logs">
<?php while ($r = @$result->fetch_array(MYSQLI_ASSOC)) {
  $group = $r['group'];
  $id = $r['id'];

  # service list
  $sql = "SELECT `name` FROM `service` LEFT JOIN `log_service_map` ON `service`.`id`=`log_service_map`.`service_id` WHERE `log_group`='$group' AND `log_id`=$id";
  $rec = $dbh->query($sql);
  $services = '';
  if (is_object($rec)) {
    while ($res = @$rec->fetch_array(MYSQLI_ASSOC)) {
      $services .= $res['name'] . ',';
    }
    $services = substr($services, 0, strlen($services)-1);
  }

  # system list
  $sql = "SELECT `name` FROM `system` LEFT JOIN `log_system_map` ON `system`.`id`=`log_system_map`.`system_id` WHERE `log_group`='$group' AND `log_id`=$id";
  $rec = $dbh->query($sql);
  $systems = '';
  if (is_object($rec)) {
    while ($res = @$rec->fetch_array(MYSQLI_ASSOC)) {
      $systems .= $res['name'] . ',';
    }
    $systems = substr($systems, 0, strlen($systems)-1);
  }

  if (strlen($services) > $serviceLimit) { $service = rtrim(substr($services, 0, $serviceLimit)).'...'; } else { $service = $services; }
  if (strlen($r['subject']) > $subjectLimit) { $subject = rtrim(substr($r['subject'], 0, $subjectLimit)).'...'; } else { $subject = $r['subject']; }
  if (strlen($systems) > $systemLimit) { $system = rtrim(substr($systems, 0, $systemLimit)).'...'; } else { $system = $systems; }
  if (strlen($r['detail']) > $detailLimit) { $detail = rtrim(substr($r['detail'], 0, $detailLimit)).'...'; } else { $detail = $r['detail']; }
?>
    <li<?php if ($r['notification']) { echo ' class="outage"'; } ?>>
      <span><?php echo l($r['posted'] . " - $subject", "view/$group/$id"); ?></span>
      <p><?php echo $service; if ((strlen($service) > 0) && (strlen($system) > 0)) { echo ' - '; } echo $system; ?></p><br />
      <p><?php echo htmlentities(trim_all($detail)); ?></p>
    </li>
<?php } ?>
  </ul>

<?php nav_links('center'); ?>

