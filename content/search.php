
  <form action="/search" name="search" class="search" method="post">

    <div class="left">
      <label>Keyword</label>
      <input type="text" name="keyword" <?php if (@strlen($_POST['keyword'])>0) { echo 'value="'.$_POST['keyword'].'" '; }?>/>
    </div>

    <div class="left">
      <label>Subject</label>
      <input type="text" name="subject" <?php if (@strlen($_POST['subject'])>0) { echo 'value="'.$_POST['subject'].'" '; }?>/>
    </div>

    <div class="left">
      <label>Service</label>
      <input type="text" name="service" <?php if (@strlen($_POST['service'])>0) { echo 'value="'.$_POST['service'].'" '; }?>/>
    </div>

    <div class="left">
      <label>System</label>
      <input type="text" name="system" <?php if (@strlen($_POST['system'])>0) { echo 'value="'.$_POST['system'].'" '; }?>/>
    </div>

    <div class="left">
      <label>User Name</label>
      <input type="text" name="user" <?php if (@strlen($_POST['user'])>0) { echo 'value="'.$_POST['user'].'" '; }?>/>
    </div>

    <div class="left">
      <label>Type</label>
      <select name="type" id="type">
        <option value=""></option>
        <option value="change">Change</option>
        <option value="emergency-fix">Emergency Fix</option>
        <option value="outage">Incident/Outage</option>
        <option value="release">Release</option>
      </select>
    </div>

    <div class="left">
      <label>Team</label>
      <input type="text" name="team" <?php if (@strlen($_POST['team'])>0) { echo 'value="'.$_POST['team'].'" '; }?>/>
    </div>

    <div class="left">
      <label>JIRA</label>
      <input type="text" name="jira" <?php if (@strlen($_POST['jira'])>0) { echo 'value="'.$_POST['jira'].'" '; }?>/>
    </div>

    <div class="left">
      <label>After Date</label>
      <input type="text" name="after" <?php if (@strlen($_POST['after'])>0) { echo 'value="'.$_POST['after'].'" '; }?>/>
    </div>

    <div class="left">
      <label>Before Date</label>
      <input type="text" name="before" <?php if (@strlen($_POST['before'])>0) { echo 'value="'.$_POST['before'].'" '; }?>/>
    </div>
<!--
    <div class="left">
      <label>Date</label>
      <select name="date">
        <option value="posted">Posted</option>
        <option value="event">Event</option>
      </select>
    </div>
-->
    <div class="left">
      <label>API</label>
      <input type="radio" name="api" value="1" checked />Include
      <input type="radio" name="api" value="0" />Exclude
      <input type="radio" name="api" value="-1" />Only
    </div>

    <div class="left">
      <label>Notifications</label>
      <input type="radio" name="outage" value="1" checked />Include
      <input type="radio" name="outage" value="0" />Exclude
      <input type="radio" name="outage" value="-1" />Only
    </div>

    <div class="break"></div>

    <input type="submit" name="search" value="Search" />

  </form>

<?php if (@strlen($where) > 0) { include('content/list.php'); } ?>

