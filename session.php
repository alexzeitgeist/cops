<?php

require('session_class.php');

$session = new session();
$session->start();

if ($session->isValidSession() === false)
{
	$session->destroy();
	$session->start();
}

$userInfo = $session->fetchUserInfo();
$token = $session->getToken();
