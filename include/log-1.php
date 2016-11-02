<?php
/**
 * log implementation
 *
 */

class log {
  private $dbh;

  # fields which are extracted for the incident table
  private $incident_fields = array(
    'status', 'severity', 'category', 'manager', 'password', 'duration',
    'downtime', 'classification', 'update', 'sms'
  );

  # define fields and attributes
  #  array key is the name of the field in the html form
  private $log_fields = array(
    'notification' => array('required'=>0, 'type'=>'checkbox'),
    'email' => array('required'=>0, 'type'=>'ignore'),
    'extra' => array('required'=>0, 'type'=>'ignore'),
    'guest' => array('required'=>0, 'type'=>'ignore'),
    'guest_name' => array('required'=>1, 'type'=>'text', 'depends_on'=>'guest'),
    'status' => array('required'=>1, 'type'=>'text', 'values'=>array('new', 'update', 'final'), 'depends_on'=>'notification'),
    'severity' => array(
      'required'=>1,
      'type'=>'text',
      'values'=>array('investigation', 'minuscule', 'minor', 'major', 'crisis'),
      'depends_on'=>'notification'
    ),
    'region' => array('required'=>0, 'type'=>'text', 'values'=>array('asia', 'na', 'eu')),
    'environment' => array(
      'required'=>0,
      'type'=>'text',
      'values'=>array('beta', 'integration', 'production')
    ),
    'category' => array('required'=>0, 'type'=>'text', 'depends_on'=>'notification'),
    'manager' => array('required'=>1, 'type'=>'text', 'depends_on'=>'notification', 'default'=>'$auth_user'),
    'password' => array('required'=>1, 'type'=>'text', 'depends_on'=>'notification'),
    'duration' => array('required'=>1, 'type'=>'int', 'depends_on'=>'notification'),
    'downtime' => array('required'=>1, 'type'=>'int', 'depends_on'=>'notification'),
    'end_date' => array('required'=>0, 'type'=>'date', 'depends_on'=>'notification'),
    'classification' => array(
      'required'=>1,
      'type'=>'text',
      'values'=>array('scheduled', 'engineering', 'operations', 'vendor', 'external', 'other'),
      'depends_on'=>'notification'
    ),
    'update' => array('required'=>1, 'type'=>'text', 'values'=>array('na', 'none', '15', '30', '60'), 'depends_on'=>'notification'),
    'sms' => array('required'=>1, 'type'=>'text', 'depends_on'=>'notification'),
    'service' => array('required'=>0, 'type'=>'text'),
    'system' => array('required'=>0, 'type'=>'text'),
    'start_date' => array('required'=>0, 'type'=>'date'),
    'team' => array('required'=>0, 'type'=>'text'),
    'jira' => array('required'=>0, 'type'=>'text'),
    'links' => array('required'=>0, 'type'=>'text'),
    'subject' => array('required'=>1, 'type'=>'text'),
    'type' => array('required'=>1, 'type'=>'text', 'values'=>array('change', 'emergency-fix', 'outage', 'release')),
    'cc' => array('required'=>0, 'type'=>'text'),
    'detail' => array('required'=>1, 'type'=>'text'),
    'submit' => array('required'=>0, 'type'=>'ignore', values=>array('Save')),
    'version' => array('required'=>1, 'type'=>'ignore')
  );

  # placeholder text that will be ignored if posted with the form
  private $placeholder=array('Describe change here...');

  # process queue for items to be linked after a log is created
  private $queue;

  public $version = '1';

  public function __construct (&$handle) {
    if (!is_object($handle)) die('Unable to log initialize object: missing argument');
    $this->dbh =& $handle;
  }

  public function get($id, $group = 'WL')
  /* get a log entry (record)
   *
   * returns: array
   *
   */
  {
    $id = intval($id);
    $group = $this->dbh->real_escape_string($group);

    $sql = "SELECT * FROM `log` WHERE `group`='$group' AND `id`=$id";
    $r = $this->dbh->query($sql);
    $row = @$r->fetch_array(MYSQLI_ASSOC);

    if (! is_array($row)) return false;

    # lookup user info
    $r = $this->dbh->query('SELECT `username`, `first`, `last` FROM `user` WHERE `id`='.$row['user_id']);
    $result = @$r->fetch_array(MYSQLI_ASSOC);
    if ($result['username'] == 'guest') {
      $row['name'] = $row['guest_name'] . ' (Guest)';
    } else {
      $row['name'] = $result['first'] . ' ' . $result['last'];
    }

    # link logs
    $row['links'] = array();
    $sql = "SELECT `ref_group` AS 'group', `ref_id` AS 'id' FROM `log_map`
      WHERE `log_group`='$group' AND `log_id`=$id
      UNION
      SELECT `log_group` AS 'group', `log_id` AS 'id' FROM `log_map`
      WHERE `ref_group`='$group' AND `ref_id`=$id
      ORDER BY `id`";
    $r = $this->dbh->query($sql);
    if (is_object($r)) {
      while ($result = @$r->fetch_array(MYSQLI_ASSOC)) {
        $row['links'][count($row['links'])] = array('group'=>$result['group'], 'id'=>$result['id']);
      }
    }

    # service list
    $row['services'] = array();
    $sql = "SELECT `name` FROM `service` LEFT JOIN `log_service_map` ON `service`.`id`=`log_service_map`.`service_id` WHERE `log_group`='$group' AND `log_id`=$id";
    $r = $this->dbh->query($sql);
    if (is_object($r)) {
      while ($result = @$r->fetch_array(MYSQLI_ASSOC)) {
        $row['services'][count($row['services'])] = $result['name'];
      }
    }

    # system list
    $row['systems'] = array();
    $sql = "SELECT `name` FROM `system` LEFT JOIN `log_system_map` ON `system`.`id`=`log_system_map`.`system_id` WHERE `log_group`='$group' AND `log_id`=$id";
    $r = $this->dbh->query($sql);
    if (is_object($r)) {
      while ($result = @$r->fetch_array(MYSQLI_ASSOC)) {
        $row['systems'][count($row['systems'])] = $result['name'];
      }
    }

    # team list
    $row['teams'] = array();
    $sql = "SELECT `name` FROM `team` LEFT JOIN `log_team_map` ON `team`.`id`=`log_team_map`.`team_id` WHERE `log_group`='$group' AND `log_id`=$id";
    $r = $this->dbh->query($sql);
    if (is_object($r)) {
      while ($result = @$r->fetch_array(MYSQLI_ASSOC)) {
        $row['teams'][count($row['teams'])] = $result['name'];
      }
    }

    return $row;
  }

  private function get_id($table, $field, $value) {
    $result = $this->dbh->query("SELECT `id` FROM `$table` WHERE `$field`='$value'");
    if (is_object($result) !== false) {
      $r = $result->fetch_array(MYSQLI_ASSOC);
      if (db_qcheck($r)) {
        return $r['id'];
      }
    }
    return false;
  }

  private function handle_environment($list) {
    return $this->handle_generic('environment', $list);
  }

  private function handle_generic($table, $list) {
    if (strlen($list) == 0) { return false; }
    $a = explode(',', str_replace(' ', '', $list));
    if (count($a) == 0) { return false; }
    foreach ($a as $value) {
      $id = $this->get_id($table, 'name', $value);
      if ($id === false) {
        $this->dbh->query("INSERT INTO `$table` SET `name`='$value'");
        $id = $this->get_id($table, 'name', $value);
        if ($id === false) { die("Error creating object '$value'"); }
      }
      $this->queue[$table][count($this->queue[$table])] = $id;
    }
    return true;
  }

  private function handle_jira($list) {
    if (strlen($list) == 0) { return false; }
    $a = explode(',', str_replace(' ', '', $list));
    if (count($a) == 0) { return false; }
    $jira = '';
    foreach ($a as $value) {
      if (preg_match('/^[A-Za-z]+-[0-9]+$/', $value) !== 1) {
        message("ignoring invalid jira id '$value'", 'warn');
      } else {
        $jira .= strtoupper($value) . ',';
      }
    }
    return array('jira'=>substr($jira, 0, strlen($jira)-1));
  }

  private function handle_links($list) {
    if (strlen($list) == 0) { return false; }
    $a = explode(',', str_replace(' ', '', $list));
    if (count($a) == 0) { return false; }
    foreach ($a as $value) {
      if (preg_match('/^[A-Za-z]+-[0-9]+$/', $value) !== 1) {
        message("ignoring invalid log id '$value'", 'warn');
      } else {
        $this->queue['log'][count($this->queue['log'])] = strtoupper($value);
      }
    }
    return true;
  }

  private function handle_service($list) {
    return $this->handle_generic('service', $list);
  }

  private function handle_system($list) {
    return $this->handle_generic('system', preg_replace('/ +/', ', ', str_replace(',', ' ', expand_parameters($list))));
  }

  private function handle_team($list) {
    return $this->handle_generic('team', preg_replace('/ +/', ', ', str_replace(',', ' ', expand_parameters($list))));
  }

  private function handle_region($list) {
    return $this->handle_generic('region', $list);
  }

  private function init_queue() {
    $this->queue = array(
      'environment' => array(),
      'log' => array(),
      'service' => array(),
      'system' => array(),
      'team' => array(),
      'region' => array()
    );
  }

  private function link_records($group, $id) {
    if (@strlen($id) === 0) { return false; }
    foreach($this->queue as $type=>$arr) {
      if ($type != 'log') { $table = "log_".$type; } else { $table = $type; }
      foreach (array_unique($arr) as $record_id) {
        if ($type != 'log') {
          $sql = "INSERT INTO `".$table."_map` SET `log_group`='$group', `log_id`=$id, `".$type."_id`=$record_id";
        } else {
          $l = explode('-', $record_id);
          $sql = "INSERT INTO `".$table."_map` SET `log_group`='$group', `log_id`=$id, `ref_group`='" . $l[0] . "', `ref_id`=" . $l[1];
        }
        if ($this->dbh->query($sql) !== true) {
          message("Error linking record for '$type' with id '$record_id' to new log", 'crit');
        }
      }
    }
  }

  /**
   * process post form
   *
   */
  public function post($data, $api = 0, $group = 'WL') {
    global $auth_user;

    $ret_val = true;

    # build post array
    $incident = array();
    $row = array();

    # reset deterministic settings
    $this->init_queue();

    # validate posted fields and dependencies
    foreach($data as $key=>$value) {

      if (array_key_exists($key, $this->log_fields)) {

        # check dependency
        if (array_key_exists('depends_on', $this->log_fields[$key])) {
          if (! (array_key_exists($this->log_fields[$key]['depends_on'], $data) && $data[$this->log_fields[$key]['depends_on']] == '1')) {
            continue;
          }
        }

        if (in_array($value, $this->placeholder)) { $value = ''; }

        if (($this->log_fields[$key]['required'] == 1) && (strlen($value) == 0)) {
          if (@strlen($this->log_fields[$key]['default']) > 0) {
            if (substr($this->log_fields[$key]['default'], 0, 1) == '$') {
              $value = $GLOBALS[substr($this->log_fields[$key]['default'], 1, strlen($this->log_fields[$key]['default']))];
            } else {
              $value = $this->log_fields[$key]['default'];
            }
          } else {
            message("Error: '$key' is required!", 'warn');
            $ret_val=false;
          }
        }

      } else {
        message("Error: '$key' is not a valid argument!", 'warn');
        $ret_val=false;
      }

      # sanitize and validate input
      switch ($this->log_fields[$key]['type']) {
      case 'checkbox':
        $value = intval($value);
        break;
      case 'date':
        $r = @date_parse($value);
        if (!is_array($r) || (count($r['errors']) > 0)) {
          $value = null;
        } else {
            $value = $r['year']
            . '-' . str_pad($r['month'], 2, '0', STR_PAD_LEFT)
            . '-' . str_pad($r['day'], 2, '0', STR_PAD_LEFT)
            . ' ' . str_pad($r['hour'], 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($r['minute'], 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($r['second'], 2, '0', STR_PAD_LEFT);
        }
        break;
      case 'ignore':
        continue 2;
        break;
      case 'text':
        if (is_array($value)) {
          foreach ($value as $k=>$v) {
            $value[$k]=$this->dbh->real_escape_string($v);
          }
        } else {
          $value = $this->dbh->real_escape_string($value);
        }
        if (@strlen($value) == 0) { $value = null; }
        break;
      case 'int':
        $value = intval($value);
        break;
      default:
        message("Error: '$key' is misconfigured", 'warn');
        $value = '';
        break;
      }

      # the following fields require special processing
      #   jira

      $func = "handle_".$key;
      if (method_exists($this, $func)) {
        $result = $this->$func($value);
        if (is_array($result)) { $new = array_merge($row, $result); $row = $new; }
      } elseif (in_array($key, $this->incident_fields)) {
        if (! is_null($value)) { $incident[$key] = $value; }
      } else {
        if (! is_null($value)) { $row[$key] = $value; }
      }

    }

    # link user information
    $id = $this->get_id('user', 'username', $auth_user);
    if ($id === false) die('Error retreiving user id number');
    $row['user_id'] = $id;

    # add ip data
    # $row['user_ip'] = ip2long($_SERVER['REMOTE_ADDR']);

    #var_dump($this->queue); echo "<br /><br />\n\n";
    #var_dump($data); echo "<br /><br />\n\n";
    #var_dump($row); echo "<br /><br />\n\n";
    #var_dump($incident); echo "<br /><br />\n\n";

    if ($ret_val === false) return false;

    # build insert statement
    $sql = "INSERT INTO `log` (`group`,`" . implode("`,`", array_keys($row)) . "`) VALUES ('$group','" . implode("','", array_values($row)) . "')";

    # insert record
    if ($this->dbh->query($sql) === true) {
      $log_id = $this->dbh->insert_id;
      $this->link_records($group, $log_id);
    } else {
      message('Error creating record', 'crit');
      $ret_val = false;
    }

    if ($ret_val == true) { return $log_id; }
    return false;
  }
}

?>
