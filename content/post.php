  <script language="javascript" src="/assets/postform.js"></script>

  <form action="/post" class="page" method="post">

    <input type="hidden" name="version" value="1" />

<?php if ($auth_user == 'guest') { ?>
    <input type="hidden" name="guest" value="1" />

    <div class="left twothirds">
      <label>*Name</label>
      <input name="guest_name" type="text" placeholder="Please enter your name, e.g. first.last"<?php if (@isset($_POST['guest_name'])) { echo ' value="'.$_POST['guest_name'].'"'; } ?> >
    </div>
<?php } ?>

    <div class="right">
      <input type="checkbox" name="notification" id="chkOutage" onchange="outageClick(this)" value="1" <?php if ($_POST['notification'] == 1) { echo "checked "; } ?>/>Log an <em>Incident</em> or <em>Outage</em><br />
      <input type="checkbox" name="email" id="chkEmail" onchange="toggleHidden(this, 'maillog')" value="1" <?php if ($_POST['email'] == 1) { echo "checked "; } ?>/>E-mail Change Log<br />
      <input type="checkbox" name="extra" id="chkExtra" onchange="toggleHidden(this, 'extra')" value="1" <?php if ($_POST['extra'] == 1) { echo "checked "; } ?>/>Show Extra Options
    </div>

    <div class="break"></div>

    <div class="crit hidden highlight" id="outage">

      <h2>Incident Notification</h2>

      <div class="left sixth">
        <label>*Status</label>
        <select name="status">
          <option value=""></option>
          <option value="new"<?php if ($_POST['status'] == 'new') { echo " selected "; } ?>>New</option>
          <option value="update"<?php if ($_POST['status'] == 'update') { echo " selected "; } ?>>Update</option>
          <option value="final"<?php if ($_POST['status'] == 'final') { echo " selected "; } ?>>Final</option>
        </select>
      </div>

      <div class="left sixth">
        <label>*Severity</label>
        <select name="severity">
          <option value=""></option>
          <option value="investigation"<?php if ($_POST['severity'] == 'investigation') { echo " selected "; } ?>>Investigating...</option>
          <option value="minuscule"<?php if ($_POST['severity'] == 'minuscule') { echo " selected "; } ?>>Miniscule</option>
          <option value="minor"<?php if ($_POST['severity'] == 'minor') { echo " selected "; } ?>>Minor</option>
          <option value="major"<?php if ($_POST['severity'] == 'major') { echo " selected "; } ?>>Major</option>
          <option value="crisis"<?php if ($_POST['severity'] == 'crisis') { echo " selected "; } ?>>Crisis</option>
        </select>
      </div>

      <div class="left third">
        <label>Region</label>
        <input type="checkbox" name="region[]" value="asia" <?php if (@in_array('asia', $_POST['region'])) { echo "checked "; } ?>/>Asia
        <input type="checkbox" name="region[]" value="na" <?php if (@in_array('na', $_POST['region'])) { echo "checked "; } ?>/>North America<br />
        <input type="checkbox" name="region[]" value="eu" <?php if (@in_array('eu', $_POST['region'])) { echo "checked "; } ?>/>Europe<br />
      </div>

      <div class="left third">
        <label>Environment</label>
        <input type="checkbox" name="environment[]" value="beta" <?php if (@in_array('beta', $_POST['environment'])) { echo "checked "; } ?>/>Beta
        <input type="checkbox" name="environment[]" value="integration" <?php if (@in_array('integration', $_POST['environment'])) { echo "checked "; } ?>/>Integration<br />
        <input type="checkbox" name="environment[]" value="production" <?php if (@in_array('production', $_POST['environment'])) { echo "checked "; } ?>/>Production<br />
      </div>

      <div class="left third">
        <label>Category</label>
        <input type="text" name="category" placeholder="Example: database,network" />
      </div>

      <div class="left third">
        <label>*Incident Manager</label>
        <input type="text" name="manager" placeholder="User name or blank; Default self" />
      </div>

      <div class="left third">
        <label>*Notification Password</label>
        <input type="password" name="password" />
      </div>

      <div class="clear left sixth">
        <label>*Duration</label>
        <input type="text" name="duration" placeholder="Example: 0 or 1" />
      </div>

      <div class="left sixth">
        <label>*Downtime</label>
        <input type="text" name="downtime" placeholder="Example: 0 or 4" />
      </div>

      <div class="left third">
        <label>End Date/Time</label>
        <input type="text" name="end_date" placeholder="Example: 2015-07-23 16:22" />
      </div>

      <div class="left sixth">
        <label>*Root Cause</label>
        <select name="classification">
          <option value=""></option>
          <option value="scheduled">Scheduled</option>
          <option value="engineering">Engineering</option>
          <option value="operations">Operations</option>
          <option value="vendor">Vendor</option>
          <option value="external">External</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="left sixth">
        <label>*Next Update</label>
        <select name="update">
          <option value=""></option>
          <option value="na">-</option>
          <option value="none">None</option>
          <option value="15">Within 15 min</option>
          <option value="30">Within 30 min</option>
          <option value="60">Within 60 min</option>
        </select>
      </div>

      <div class="caption">
        Enter number of minutes (round up) for duration and downtime.
      </div>

      <div class="left">
        <label>*SMS Message</label>
        <textarea name="sms" id="sms" maxlength="160" onchange="detailChangeHandler('sms')" onfocus="detailFocusHandler('sms')"></textarea>
      </div>

      <div class="caption">
        SMS messages are limited to 160 characters. Please use short, succinct sentences.
      </div>

      <div class="break"></div>

    </div>

    <div class="clear left sixth">
      <label>Service</label>
      <input name="service" type="text" placeholder="Example: HTTP"<?php if (@isset($_POST['service'])) { echo ' value="'.htmlspecialchars($_POST['service']).'"'; } ?> >
    </div>

    <div class="left sixth">
      <label>Team</label>
      <input name="team" type="text" placeholder="Example: Middleware"<?php if (@isset($_POST['team'])) { echo ' value="'.htmlspecialchars($_POST['team']).'"'; } ?> >
    </div>

    <div class="left twothirds">
      <label>System</label>
      <input name="system" type="text" placeholder="Example: na-http0{1..4}"<?php if (@isset($_POST['system'])) { echo ' value="'.htmlspecialchars($_POST['system']).'"'; } ?> >
    </div>

    <div class="caption">
      List multiple services, teams, or server names seperated by commas, no spaces. Systems support bash argument expansion.
    </div>

    <div class="highlight hidden" id="extra">

      <div class="left third">
        <label>Event Date/Time</label>
        <input name="start_date" type="text" placeholder="Example: 2015-07-23 16:22, Default: NOW"<?php if (@isset($_POST['start_date'])) { echo ' value="'.htmlspecialchars($_POST['start_date']).'"'; } ?> >
      </div>

      <div class="left third">
        <label>JIRA</label>
        <input name="jira" type="text" placeholder="Example: JIRA-14045"<?php if (@isset($_POST['jira'])) { echo ' value="'.htmlspecialchars($_POST['jira']).'"'; } ?> >
      </div>

      <div class="left third">
        <label>Linked Events</label>
        <input name="links" type="text" placeholder="Example: WL-12345"<?php if (@isset($_POST['links'])) { echo ' value="'.htmlspecialchars($_POST['links']).'"'; } ?> >
      </div>

    </div>

    <div class="clear left twothirds">
      <label>*Subject</label>
      <input name="subject" type="text" placeholder="Example: restarted httpd"<?php if (@isset($_POST['subject'])) { echo ' value="'.htmlspecialchars($_POST['subject']).'"'; } ?> >
    </div>

    <div class="right third">
      <label>*Type</label>
      <select name="type" id="type">
        <option value="change"<?php if ($_POST['type'] == "change") { echo " selected"; } ?>>Change</option>
        <option value="emergency-fix"<?php if ($_POST['type'] == "emergency-fix") { echo " selected"; } ?>>Emergency Fix</option>
        <option value="outage"<?php if ($_POST['type'] == "outage") { echo " selected"; } ?>>Incident/Outage</option>
        <option value="release"<?php if ($_POST['type'] == "release") { echo " selected"; } ?>>Release</option>
      </select>
    </div>

    <div class="highlight hidden" id="maillog">

      <div class="left">
        <label>CC</label>
        <input type="text" name="cc" placeholder="E-mail addresses, comma delimited"<?php if (@isset($_POST['cc'])) { echo ' value="'.htmlspecialchars($_POST['cc']).'"'; } ?> />
      </div>

    </div>

    <div class="left">
      <label>*Detail</label>
      <textarea name="detail" id="detail" onchange="detailChangeHandler('detail')" onfocus="detailFocusHandler('detail')"><?php if (@isset($_POST['detail'])) { echo htmlspecialchars($_POST['detail']); } ?></textarea>
      <input id="submit" name="submit" type="submit" value="Save">
    </div>

  </form>

