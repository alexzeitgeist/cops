<?php

require('config.php');

if (empty($config['cops_loginEndpoint']) || empty($config['cops_secretKey']))
{
        http_response_code(500);
        exit();
}

if (empty($_REQUEST['do']))
{
        http_response_code(500);
        exit();
}

else if ($_REQUEST['do'] === 'login')
{
	if (!empty($userInfo) && is_array($userInfo))
	{
		sendMessageAndExit('ok', 'User already logged in.');
	}

	require('sso_class.php');

	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

	$sso = new SSO($config['cops_loginEndpoint'], $config['cops_secretKey']);
	$resp = $sso->doLogin($username, $password);

	if ($sso->isValidUser($resp))
	{
		$session->regenerate();
		$session->setToken();

		$_SESSION['userid'] = $resp['userid'];
		$_SESSION['username'] = $resp['username'];
		$_SESSION['usergroupid'] = $resp['usergroupid'];
		$_SESSION['membergroupids'] = $resp['membergroupids'];

		sendMessageAndExit('ok', 'Logged in.');
	}
	else
	{
		sendMessageAndExit('fail', $resp);
	}
}
else if ($_REQUEST['do'] === 'logout')
{
	if (empty($userInfo) || $userInfo === false)
	{
		sendMessageAndExit('ok', 'User already logged out.');
	}

	$suppliedToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : false;

	if ($token === $suppliedToken)
	{
		$session->destroy();

		sendMessageAndExit('ok', 'Logged out.');
	}
	else
	{
		sendMessageAndExit('fail', 'Invalid token.');
	}
}
else
{
	http_response_code(500);
	exit();
}

function sendMessageAndExit($result, $message)
{
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array('result' => $result, 'message' => $message));
	exit();
}
