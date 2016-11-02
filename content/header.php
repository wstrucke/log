<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Activity Log</title>
  <link rel="stylesheet" href="/assets/style.css" />
</head>
<body>
  <header>Company Name</header>

<?php if ($authenticated) { ?>
  <div class="welcome"><?php echo $auth_user; ?></div>
  <div class="logout"><a href="/logout">Logout</a></div>
<?php } ?>
