<?php

namespace WS;

class WSServer implements \WS\WSInterface
{
	protected $wsClients = array();
	protected $wsDatabase;
	private $host = '127.0.0.1';
	private $port = 8888;
	private $masterSocket = null;
	private $clientSockets = array();
	private $dataChunk = 128;
	private $maxHeaderSize = 2048;

	protected $sessionSavePath = '/';
	protected $sessionFilePrefix = 'sess_';
	protected $sessionCookieName = 'PHPSESSID';

	protected $userOnSystem = array();

	private $running = true;
	private $socketOk = false;

	protected $callbackObject;
	protected $callbackPostConstruct;

	/**
	 * Conctructor of WSServer
	 *
	 * @param \WS\WSDatabase $wsDatabase
	 * @param string $host
	 * @param integer $port
	 * @param mixed $callbackObject
	 * @param string $callbackPostConstruct
	 * @param string $messageOnStarted
	 */
	public function __construct($wsDatabase, $host = '127.0.0.1', $port = 8888, $callbackObject = null, $callbackPostConstruct = null, $messageOnStarted = "")
	{
		$this->wsDatabase = $wsDatabase;
		$this->host = $host;
		$this->port = $port;

		$this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// stream_set_blocking($this->masterSocket, 0);
		// reuseable port
		socket_set_option($this->masterSocket, SOL_SOCKET, SO_REUSEADDR, 1);
		// bind socket to specified host
		socket_bind($this->masterSocket, 0, $this->port);
		// listen to port
		$this->socketOk = socket_listen($this->masterSocket);
		$this->clientSockets = array($this->masterSocket);
		$this->sessionSavePath = session_save_path();
		$this->callbackObject = $callbackObject;
		$this->callbackPostConstruct = $callbackPostConstruct;

		if ($this->socketOk && !empty($messageOnStarted)) {
			echo $messageOnStarted;
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->masterSocket);
	}

	/**
	 * Reset user on system
	 * @return void
	 */
	protected function resetUserOnSystem()
	{
		$this->userOnSystem = array();
	}
	/**
	 * Set user on system
	 * @param string $clientIndex Client index
	 * @param array $clientData
	 */
	protected function setUserOnSystem($clientIndex, $clientData)
	{
		$this->userOnSystem[$clientIndex] = $clientData;
	}

	/**
	 * Reset user on system
	 * @return void
	 */
	protected function updateUserOnSystem()
	{
		$this->resetUserOnSystem();
		foreach ($this->wsClients as $client) {
			if (isset($client->getClientData()['username'])) {
				$this->setUserOnSystem($client->getClientData()['username'], $client->getClientData());
			}
		}
	}

	/**
	 * Run service
	 */
	public function run() //NOSONAR
	{
		if ($this->socketOk) {
			$index = 0;
			$null = null; //null var
			while ($this->running) {
				// manage multiple connections
				$changed = $this->clientSockets;
				// returns the socket resources in $changed array
				if (@socket_select($changed, $null, $null, 0, 10000) < 1) {
					continue;
				}
				// check for new socket
				if (in_array($this->masterSocket, $changed)) {
					$clientSocket = socket_accept($this->masterSocket); //accpet new socket
					//stream_set_blocking($clientSocket, 0);
					$header = socket_read($clientSocket, $this->maxHeaderSize); //read data sent by the socket
					$header = trim($header, " \r\n ");
					if (strlen($header) > 2 && stripos($header, 'Sec-WebSocket-Key') !== false) {
						$index++;
						socket_getpeername($clientSocket, $remoteAddress, $remotePort); //get ip address of connected socket
						$wsClient = new \WS\WSClient(
							$index,
							$clientSocket,
							$header,
							new \WS\WSRemoteConnection($remoteAddress, $remotePort),
							new \WS\WSSessionParams($this->sessionCookieName, $this->sessionSavePath, $this->sessionFilePrefix),
							$this->callbackObject,
							$this->callbackPostConstruct
						);
						$this->clientSockets[$index] = $clientSocket; //add socket to client array
						$this->wsClients[$index] = $wsClient;
						$this->onOpen($wsClient);
						$foundSocket = array_search($this->masterSocket, $changed);
						unset($changed[$foundSocket]);
					}
				}
				if (is_array($changed)) {
					//loop through all connected sockets
					foreach ($changed as $index => $changeSocket) {
						//check for any incomming data

						$buffer = '';
						$buf1 = '';
						$nread = 0;
						do {
							$recv = @socket_recv($changeSocket, $buf1, $this->dataChunk, 0);
							if ($recv > 1) {
								$nread++;
								$buffer .= $buf1;
								if ($recv < $this->dataChunk || $recv === false) {
									break;
								}
							} else {
								break;
							}
						} while ($recv > 0);

						if ($nread > 0 && strlen($buffer) > 0) {
							socket_getpeername($changeSocket, $ip, $port);
							$decodedData = $this->hybi10Decode($buffer);
							if (isset($decodedData['type'])) {
								if ($decodedData['type'] == 'close') {
									break;
								} else {
									$this->onMessage($this->wsClients[$index], $decodedData['payload']);
									break;
								}
							} else {
								break;
							}
						}
						$buf2 = @socket_read($changeSocket, $this->dataChunk, PHP_NORMAL_READ);
						if ($buf2 === false) {
							// check disconnected client
							// remove client for $clientSockets array
							$foundSocket = array_search($changeSocket, $this->clientSockets);
							if (isset($this->wsClients[$foundSocket])) {
								$closeClient = $this->wsClients[$foundSocket];
								unset($this->clientSockets[$foundSocket]);
								unset($this->wsClients[$foundSocket]);
								$this->onClose($closeClient);
							}
						}
					}
				}
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Get first frame head
	 */
	private function getFirstFrameHead($type)
	{
		$ffh = 0;
		if ($type == 'text') {
			// first byte indicates FIN, Text-Frame (10000001):
			$ffh = 129;
		} else if ($type == 'close') {
			// first byte indicates FIN, Close Frame(10001000):
			$ffh = 136;
		} else if ($type == 'ping') {
			// first byte indicates FIN, Ping frame (10001001):
			$ffh = 137;
		} else if ($type == 'pong') {
			// first byte indicates FIN, Pong frame (10001010):
			$ffh = 138;
		}
		return $ffh;
	}

	/**
	 * Get data type from opcode
	 */
	private function getDataType($opcode)
	{
		$decodedDataType = '';
		if ($opcode == 1) {
			// text frame
			$decodedDataType = 'text';
		} else if ($opcode == 2) {
			// connection close frame
			$decodedDataType = 'binary';
		} else if ($opcode == 8) {
			// connection close frame
			$decodedDataType = 'close';
		} else if ($opcode == 9) {
			// ping frame
			$decodedDataType = 'ping';
		} else if ($opcode == 10) {
			// pong frame
			$decodedDataType = 'pong';
		}

		return $decodedDataType;
	}

	/**
	 * Encodes a frame/message according the the WebSocket protocol standard.     
	 * @param $payload
	 * @param $type
	 * @param $masked
	 * @throws \WS\WSException
	 * @return string
	 */
	public function hybi10Encode($payload, $type = 'text', $masked = true) //NOSONAR
	{
		$frameHead = array();
		$payloadLength = strlen($payload);

		$frameHead[0] = $this->getFirstFrameHead($type);
		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if ($frameHead[2] > 127) {
				throw new \WS\WSException('Invalid payload. Could not encode frame.');
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked === true) {
			// generate a random mask:
			$mask = array();
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}

			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);

		// append payload to frame:
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}
		return $frame;
	}

	/**
	 * Decodes a frame/message according to the WebSocket protocol standard.
	 *
	 * @param $data
	 * @return array
	 */
	public function hybi10Decode($data)
	{
		$unmaskedPayload = '';
		$decodedData = array();

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// close connection if unmasked frame is received:
		if ($isMasked === false) {
			// Do nothing
		}

		$decodedData['type'] = $this->getDataType($opcode);

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return array();
		}

		if ($isMasked === true) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}


	/**
	 * Method when a new client is connected
	 * @param \WS\WSClient $wsClient Chat client
	 */
	public function onOpen($wsClient)
	{
	}

	/**
	 * Method when a new client is disconnected
	 * @param $wsClient Chat client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 * @return void
	 */
	public function onClose($wsClient)
	{
	}

	/**
	 * Method when a client send the message
	 * @param $wsClient Chat client
	 * @param string $receivedText Text sent by the client
	 * @param string $ip Remote adddress or IP address of the client 
	 * @param int $port Remote port or port number of the client
	 */
	public function onMessage($wsClient, $receivedText)
	{
	}

	/**
	 * Method to send the broadcast message to all client
	 * @param \WS\WSClient $wsClient Chat client
	 * @param string $message Message to sent to all client
	 * @param array $receiverGroups Receiver
	 * @param bool $meeToo
	 * @return void
	 */
	public function sendBroadcast($wsClient, $message, $receiverGroups = null, $meeToo = false)
	{
		foreach ($this->wsClients as $client) {
			if (
				$meeToo
				|| $receiverGroups == null
				|| empty($receiverGroups)
				|| ($wsClient->getResourceId() != $client->getResourceId() && ($this->groupReceive($receiverGroups, $client->getGroupId())))
			) {
				$client->sendMessage($message);
			}
		}
	}

	/**
	 * Check if receiver is in group or not
	 *
	 * @param array $receiverGroups
	 * @param string $groupId
	 * @return bool
	 */
	public function groupReceive($receiverGroups, $groupId)
	{
		return isset($receiverGroups)
			&& is_array($receiverGroups)
			&& isset($groupId)
			&& in_array($groupId, $receiverGroups);
	}

	/**
	 * Send message
	 *
	 * @param string $textMessage
	 * @param array $receiver
	 * @return void
	 */
	public function sendMessage($textMessage, $receiver)
	{
		foreach ($this->wsClients as $client) {
			$clientData = $client->getClientData();
			if (in_array($clientData['username'], $receiver)) {
				$client->sendMessage($textMessage);
			}
		}
	}
}
