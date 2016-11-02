<?php
/**
 * view a log entry
 *
 */

function colorize($str) {
  # comments
  $str = preg_replace('/^(#.*)$/m', '<font color="#aaaaaa">$1</font>', $str);
  # prompt
  $str = preg_replace('/^([A-Za-z0-9\-]+:[~A-Za-z0-9\-_ \.]+\$)( .*)$/m', '<strong>$1</strong>$2', $str);
  $str = preg_replace('/^(\[[A-Za-z0-9\-]+(?:|@)[~A-Za-z0-9\-_ \.]+\]#)( .*)$/m', '<strong>$1</strong>$2', $str);
  return $str;
}

function make_links($str) {
  $in=array(
   '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si',
    '`((?<!//)(www\.\S+[[:alnum:]]/?))`si'
  );
  $out=array(
    '<a href="$1" rel="nofollow">$1</a> ',
    '<a href="http://$1" rel="nofollow">$1</a>'
  );
  return preg_replace($in, $out, $str);
}

include_once('include/log-1.php');

$group = $dbh->real_escape_string($target[1]);
$id = intval($target[2]);

$log = new log($dbh);
$row = $log->get($id, $group);

?>
  <ul class="right navigation withtext">
    <li><?php echo l('Next &gt;', "view/$group/".($id+1), 'Next'); ?></li>
<?php if ($id > 1) { ?>
    <li><?php echo l('&lt; Previous', "view/$group/".($id-1), 'Previous'); ?></li>
<?php } ?>
  </ul>

<?php
if (is_array($row)) {

  # lookup user info
  $name = $row['name'];

  # link tickets
  if (strlen($row['jira']) > 0) {
    $arr = explode(',', $row['jira']);
    while($n = array_pop($arr)) {
      $jira .= l($n, $settings['jira_url'] . $n) . ', ';
    }
    $jira = substr($jira, 0, strlen($jira)-2);
  }

  # link logs
  $links = '';
  foreach ($row['links'] as $arr) {
    $links .= l($arr['group'].'-'.$arr['id'], "view/".$arr['group']."/".$arr['id']) . ', ';
  }
  $links = substr($links, 0, strlen($links)-2);

  # service list
  $services = implode(', ', $row['services']);

  # system list
  $systems = implode(', ', $row['systems']);

  # team list
  $teams = implode(', ', $row['teams']);

?>
  <div class="view">
<?php if ($row['notification']) { ?>

    <div class="crit outage">
      <div class="side">
        <span>Status</span><p>New</p><br />
        <span>Severity</span><p>Under Investigation</p><br />
        <span>Incident Manager</span><p>Name</p><br />
        <span>Next Update</span><p>Within 30 minutes</p><br />
      </div>
      <div class="side">
        <span>Duration</span><p>10</p><br />
        <span>Downtime</span><p>8</p><br />
        <span>End Date</span><p></p><br />
        <span>Root Cause</span><p>Example</p><br />
      </div>
      <div class="break"></div>
      <span>Region</span><p>North America</p><br />
      <span>Environment</span><p>Production</p><br />
      <span>Category</span><p>database,network</p><br />
      <span>SMS Message</span><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis rhoncus nunc, quis tincidunt quam posuere eget. Aliquam id sapien at augue nullam sodales.</p>
    </div>
<?php } ?>

    <span>ID</span><p><?php echo $row['group'] . '-' . $row['id']; ?></p><br />
    <span>Posted By</span><p><?php echo $name; ?></p><br />
    <span>Team</span><p><?php echo $teams; ?></p><br />
    <span>Entry Date</span><p><?php echo $row['posted']; ?></p><br />
    <span>Event Date</span><p><?php echo $row['start_date']; ?></p><br />
    <span>Type</span><p><?php echo ucwords($row['type']); ?></p><br />
    <span>JIRA</span><p><?php echo $jira; ?></p><br />
    <span>Events</span><p><?php echo $links; ?></p><br />
    <span>Service</span><p><?php echo $services; ?></p><br />
    <span>System</span><p><?php echo $systems; ?></p><br />

    <div class="break"></div>

    <span>Subject</span><p><?php echo $row['subject']; ?></p><br />
    <span>Detail</span><p class="code"><?php echo colorize(str_replace('  ', '&nbsp;&nbsp;', nl2br(make_links(htmlentities($row['detail']))))); ?></p>

  </div>

<?php
} else {
  echo "<br /><br /><br />\n\n\n";
  message('Invalid record ID', 'info');
}
?>
