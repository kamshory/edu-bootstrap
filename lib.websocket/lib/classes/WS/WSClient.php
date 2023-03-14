<?php

namespace WS;

class WSClient //NOSONAR
{
	private $socket;
	private $wsRemoteConnection;
	private $headers = array();
	private $cookies = array();
	private $sessions = array();
	private $sessionID = '';
	private $resourceId = 0;
	private $httpVersion = '';
	private $method = '';
	private $uri = '';
	private $path = '';
	private $query = array();
	private $clientData = array();
	private $host = "";
	private $port = 0;
	private $headerInfo = array();
	private $groupId = "";
	private $username = "";
	private $name = "";
	public $sessionParams;


	/**
	 * @param string $resourceId Resource ID 
	 * @param \Socket $socket 
	 * @param string $headers
	 * @param \WS\WSRemoteConnection $wsRemoteConnection
	 * @param \WS\WSSessionParams $sessionParams 
	 * @param object $callbackObject,
	 * @param string $callbackPostConstruct
	 */
	public function __construct($resourceId, $socket, $headers, $wsRemoteConnection, $sessionParams, $callbackObject, $callbackPostConstruct)
	{
		$this->resourceId = $resourceId;
		$this->socket = $socket;
		$this->wsRemoteConnection = $wsRemoteConnection;

		$headerInfo = \WS\WSUtil::parseRawHeaders($headers);

		$this->parseHeaders($headerInfo);

		$this->performHandshaking($headers, $this->host, $this->port);

		if (isset($this->headers['cookie'])) {
			$this->cookies = \WS\WSUtil::parseRawCookies($this->headers['cookie']);
		}

		if ($sessionParams === null) {
			$this->setSessionParams(new \WS\WSSessionParams(null, session_save_path(), null));
		} else {
			$this->setSessionParams($sessionParams);
		}
		$sessionName = $this->getSessionParams()->getSessionCookieName();
		if (isset($this->cookies[$sessionName])) {
			$this->setSessionID($this->cookies[$sessionName]);
		}

		$this->setSessions(\WS\WSUtil::getSessions($this->getSessionID(), $this->getSessionParams()));

		if ($callbackObject != null && $callbackPostConstruct != null) {
			$this->clientData = call_user_func(array($callbackObject, $callbackPostConstruct), $this);
			$this->groupId = $this->clientData['group_id'];
			$this->username = $this->clientData['username'];
		}
	}

	/**
	 * Parse header info
	 *
	 * @param array $headerInfo
	 * @return void
	 */
	private function parseHeaders($headerInfo)
	{
		$port = 0;
		$host = "";

		$this->headerInfo = $headerInfo;

		$headers = $headerInfo['headers'];

		$this->headers = $headerInfo['headers'];
		$this->method = $headerInfo['method'];
		$this->uri = $headerInfo['uri'];
		$this->path = $headerInfo['path'];
		$this->query = $headerInfo['query'];
		$this->httpVersion = $headerInfo['version'];

		if (isset($headers['x-forwarded-host'])) {
			$host = $headers['x-forwarded-host'];
		} else if (isset($headers['x-forwarded-server'])) {
			$host = $headers['x-forwarded-server'];
		} else {
			$host = $headers['host'];
		}
		if (stripos($host, ":") !== false) {
			$arrHost = explode(":", $host);
			$host = $arrHost[0];
			$port = (int) $arrHost[1];
		} else {
			$port = 443;
		}
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * Send message
	 *
	 * @param string $message
	 * @return void
	 */
	public function sendMessage($message)
	{
		$maskedMessage = \WS\WSUtil::mask($message);
		@socket_write($this->socket, $maskedMessage, strlen($maskedMessage));
	}

	/**
	 * Handshake new client
	 * @param $recevedHeader Request header sent by the client
	 * @param $client_conn Client connection
	 * @param $host Host name of the websocket server
	 * @param $port Port number of the websocket server
	 */
	public function performHandshaking($recevedHeader, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $recevedHeader);
		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}
		if (isset($headers['Sec-WebSocket-Key'])) {
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			//hand shaking header
			$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n"
				. "Upgrade: websocket\r\n"
				. "Connection: Upgrade\r\n"
				. "WebSocket-Origin: $host\r\n"
				. "WebSocket-Location: ws://$host:$port\r\n"
				. "Sec-WebSocket-Accept: $secAccept\r\n"
				. "Access-Control-Allow-Origin: *\r\n"
				. "X-Engine: PlanetChat\r\n\r\n";
			socket_write($this->socket, $upgrade, strlen($upgrade));
		}
	}

	public function login()
	{
		return true;
	}


	/**
	 * Get the value of sessionParams
	 * @return \WS\WSSessionParams
	 */
	public function getSessionParams()
	{
		return $this->sessionParams;
	}

	/**
	 * Set the value of sessionParams
	 * @param \WS\WSSessionParams $sessionParams
	 * @return self
	 */
	public function setSessionParams($sessionParams)
	{
		$this->sessionParams = $sessionParams;

		return $this;
	}

	/**
	 * Get the value of headerInfo
	 * @return array
	 */
	public function getHeaderInfo()
	{
		return $this->headerInfo;
	}

	/**
	 * Get the value of \WSRemoteConnection
	 */
	public function getRemoteConnection()
	{
		return $this->wsRemoteConnection;
	}

	/**
	 * Get the value of resourceId
	 * @return int
	 */
	public function getResourceId()
	{
		return $this->resourceId;
	}

	/**
	 * Get the value of httpVersion
	 * @return string
	 */
	public function getHttpVersion()
	{
		return $this->httpVersion;
	}

	/**
	 * Get the value of method
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Get the value of uri
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Get the value of groupId
	 * @return string
	 */
	public function getGroupId()
	{
		return $this->groupId;
	}

	/**
	 * Get the value of path
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Get the value of query
	 * @return array
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Get the value of sessionID
	 * @return string
	 */
	public function getSessionID()
	{
		return $this->sessionID;
	}

	/**
	 * Set the value of sessionID
	 *
	 * @return self
	 */
	public function setSessionID($sessionID)
	{
		$this->sessionID = $sessionID;

		return $this;
	}

	/**
	 * Get the value of sessions
	 * @return array
	 */
	public function getSessions()
	{
		return $this->sessions;
	}

	/**
	 * Set the value of sessions
	 *
	 * @return self
	 */
	public function setSessions($sessions)
	{
		$this->sessions = $sessions;

		return $this;
	}

	/**
	 * Get the value of clientData
	 * @return array
	 */
	public function getClientData()
	{
		return $this->clientData;
	}

	/**
	 * Set the value of clientData
	 *
	 * @return self
	 */
	public function setClientData($clientData)
	{
		$this->clientData = $clientData;

		return $this;
	}

	/**
	 * Get the value of cookies
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Get the value of username
	 */
	public function getUsername()
	{
		return $this->username;
	}


	/**
	 * Get the value of name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the value of name
	 *
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}
}
