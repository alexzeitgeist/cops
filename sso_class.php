<?php

class SSO
{
	protected $_loginEndpoint = null;
	protected $_secretKey = null;

	protected $_username = null;
	protected $_password = null;
	protected $_ipaddress = null;

	protected $_debug = null;
	protected $_headers = array();

	public function __construct($loginEndpoint, $secretKey, $debug = false)
	{
		$this->_loginEndpoint = $loginEndpoint;
		$this->_secretKey = $secretKey;
		$this->_debug = $debug;
	}

	public function doLogin($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
		$this->_ipaddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		$message = $this->_username . $this->_password;

		$this->sign($message);

		$post = array(
			'vb_login_username' => $this->_username,
			'vb_login_password' => $this->_password,
			'vb_ip_address' => $this->_ipaddress,
		);

		$ch = curl_init();

		if ($this->_debug)
		{
			curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1_2');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_URL, $this->_loginEndpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$output = curl_exec($ch);

		if (curl_errno($ch))
		{
			$error = 'Error: ' . curl_error($ch);
			curl_close($ch);

			return $error;
		}

		curl_close($ch);

		$payload = json_decode($output, true);

		if ($this->_debug)
		{
			var_dump($payload);
		}

		return $payload;
	}

	public function isValidUser($payload)
	{
		if ((array) $payload !== $payload)
		{
			return false;
		}

		return true;
	}

	private function sign($message)
	{
		$nonce = (int)(microtime(true) * 1e6);
		$hmacKey = hash_hmac('sha256', $this->_secretKey, $nonce);
		$hmac = hash_hmac('sha256', $message, $hmacKey);

		$this->_headers[] = 'Signature: ' . $hmac;
		$this->_headers[] = 'Nonce: ' . $nonce;
	}
}
