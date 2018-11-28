<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Secure Chat | Login</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/styles.css'); ?>">
</head>
<body>
 
<div id="container">
	<h1>Secure chat application</h1>
 
	<div id="body">
		<?php if ($err != '') { ?>
		<h3>Warning!</h3>
		<pre><?php echo $err; ?></pre>
		<hr />
		<?php } ?>
		<?php if ($info != '') { ?>
		<pre><?php echo $info; ?></pre>
		<hr />
		<?php } ?>
		<h2>Login</h2>
		<?php echo form_open(); ?>
			<div><label>Username</label></div>
			<div><?php echo form_input(array('name' => 'username', 'placeholder' => 'Username')); ?></div>
			<div><label>Password</label></div>
			<div><?php echo form_password(array('name' => 'password', 'placeholder' => 'Password', 'autocomplete' => 'new-password')); ?></div>
			<div><?php echo form_submit(array('name' => 'login', 'value' => 'Login')); ?></div>
		<?php echo form_close(); ?>
		<hr />
		<h2>Register</h2>
		<?php echo form_open(); ?>
			<div><label>Name</label></div>
			<div><?php echo form_input(array('name' => 'name', 'placeholder' => 'Your Name', 'maxlength' => 32)); ?></div>
			<div><label>Username</label></div>
			<div><?php echo form_input(array('name' => 'username', 'placeholder' => 'Username', 'maxlength' => 16)); ?></div>
			<div><label>Password</label></div>
			<div><?php echo form_password(array('name' => 'password', 'placeholder' => 'Password', 'autocomplete' => 'new-password')); ?></div>
			<div><?php echo form_submit(array('name' => 'register', 'value' => 'Register')); ?></div>
		<?php echo form_close() ?>
	</div>
 
	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : ''; ?></p>
</div>
 
</body>
</html>