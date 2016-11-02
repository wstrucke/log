<?php

  $r = $dbh->query('SELECT COUNT(*) FROM log');
  if (is_object($r)) {
    $a = $r->fetch_array(MYSQLI_NUM);
    $total_records = $a[0];
  }

  $r = $dbh->query('SELECT COUNT(*) FROM log WHERE `api`=1');
  if (is_object($r)) {
    $a = $r->fetch_array(MYSQLI_NUM);
    $total_api_records = $a[0];
  }

  $r = $dbh->query('SELECT COUNT(*) FROM log WHERE `api`=0');
  if (is_object($r)) {
    $a = $r->fetch_array(MYSQLI_NUM);
    $total_manual_records = $a[0];
  }

  $r = $dbh->query('SELECT COUNT(*) FROM log WHERE `posted` >= CURDATE()');
  if (is_object($r)) {
    $a = $r->fetch_array(MYSQLI_NUM);
    $total_today = $a[0];
  }

  $r = $dbh->query('SELECT COUNT(*) FROM log WHERE `posted` >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)');
  if (is_object($r)) {
    $a = $r->fetch_array(MYSQLI_NUM);
    $total_week = $a[0];
  }

?>
  <p>Welcome to the Activity Log!</p>

  <div class="page">
    Total entries: <?php echo "$total_records ($total_api_records API / $total_manual_records Regular)"; ?><br />
    Total posted today: <?php echo $total_today; ?><br />
    Total posted this week: <?php echo $total_week; ?><br />
  </div>
