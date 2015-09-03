<?php

class Session
{
	private $_name = null;
	private $_cookie = null;

	public function __construct($name = 'sid')
	{
		$this->_name = $name;

		$this->_cookie = array (
			'lifetime'	=> 0,
			'path'		=> ini_get('session.cookie_path'),
			'domain'	=> ini_get('session.cookie_domain'),
			'secure'	=> isset($_SERVER['HTTPS']),
			'httponly'	=> true
		);

		$this->_setup();
	}

	public function start()
	{
		if (session_id() === '')
		{
			if (session_start())
			{
				return true;
			}
		}

		return false;
	}

	public function regenerate()
	{
		return session_regenerate_id(true);
	}

	public function destroy()
	{
		if (session_id() !== '')
		{
			$_SESSION = array();

			setcookie(
				$this->_name,
				'',
				time() - 42000,
				$this->_cookie['path'],
				$this->_cookie['domain'],
				$this->_cookie['secure'],
				$this->_cookie['httponly']
			);

			return session_destroy();
		}

		return false;
	}

	public function setToken()
	{
		$token = sha1(uniqid(rand(), true));
		$_SESSION['_token'] = $token;
	}

	public function getToken()
	{
		$token = isset($_SESSION['_token']) ? $_SESSION['_token'] : false;

		return $token;
	}

	public function isValidToken($token)
	{
		return $_SESSION['_token'] === $token;
	}

	public function isValidSession($ttl = 30, $fingerprint = true)
	{
		return !($this->_isExpired($ttl) || $this->_isHijacked($fingerprint));
	}

	public function fetchUserInfo()
	{
		if ($this->isLoggedIn())
		{
			return array(
				'userid' => $_SESSION['userid'],
				'username' => $_SESSION['username'],
				'usergroupid' => $_SESSION['usergroupid'],
				'membergroupids' => $_SESSION['membergroupids']
			);
		}

		return false;
	}

	public function isLoggedIn()
	{
		return isset($_SESSION['userid']);
	}

	private function _setup()
	{
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_trans_sid', 0);

		session_name($this->_name);

		session_set_cookie_params(
			$this->_cookie['lifetime'],
			$this->_cookie['path'],
			$this->_cookie['domain'],
			$this->_cookie['secure'],
			$this->_cookie['httponly']
		);
	}

	private function _isExpired($ttl = 30)
	{
		$last = isset($_SESSION['_lastActivity']) ? $_SESSION['_lastActivity'] : false;

		if ($last !== false && (time() - $last) > ($ttl * 60))
		{
			return true;
		}

		$_SESSION['_lastActivity'] = time();

		return false;
	}

	private function _isHijacked($fingerprint = true)
	{
		if ($fingerprint === false)
		{
			return true;
		}

		$hash = sha1($_SERVER['HTTP_USER_AGENT'] . $this->_maskIp($_SERVER['REMOTE_ADDR']));

		if (isset($_SESSION['_fingerprint']))
		{
			return $_SESSION['_fingerprint'] !== $hash;
		}

		$_SESSION['_fingerprint'] = $hash;

		return false;
	}

	private function _maskIp($ip)
	{
		$ipv6 = (strstr($ip, ':') !== false);

		$in_addr = inet_pton($ip);

		if ($ipv6)
		{
			// Not sure how many to mask for IPv6, opinions?
			$mask = inet_pton('ffff:ffff:ffff:ffff:ffff:0:0:0');
		}
		else
		{
			$mask = inet_pton('255.255.0.0');
		}

		$final = inet_ntop($in_addr & $mask);

		return str_replace(array(':0', '.0'), array(':x', '.x'), $final);
	}
}
