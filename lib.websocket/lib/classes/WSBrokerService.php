<?php

class WSBrokerService extends WSServer implements WSInterface {
	private $testMember = array();
	public function __construct($wsDatabase, $host = '127.0.0.1', $port = 8888, $callbackObject = null, $callbackPostConstruct = null, $messageOnStarted = "")
	{
		parent::__construct($wsDatabase, $host, $port, $callbackObject, $callbackPostConstruct, $messageOnStarted);
	}
	

	/**
	 * Add student to member test list
	 * @param stdClass $student
	 */
	private function memberTestAdd($student, $testId)
	{
		if(!empty($student->student_id))
		{
			$student_id = $student->student_id;
			if(!isset($this->testMember[$testId]))
			{
				$this->testMember[$testId] = array();
			}
			if(!isset($this->testMember[$testId][$student_id]))
			{
				$this->testMember[$testId][$student_id] = array();
			}
			$this->testMember[$testId][$student_id][$student->resourceId] = $student;
		}

	}

	/**
	 * Remove student from member test list
	 * @param stdClass $student
	 */
	private function memberTestRemove($student, $testId) //NOSONAR
	{
		if(!empty($student->student_id))
		{
			$student_id = $student->student_id;
			if(isset($this->testMember[$testId]))
			{
				if(isset($this->testMember[$testId][$student_id]))
				{
					foreach($this->testMember[$testId][$student_id] as $key=>$member)
					{
						if($member->resourceId == $student->resourceId)
						{
							unset($this->testMember[$testId][$student_id][$key]);
							break;
						}
					}
				}
				if(empty($this->testMember[$testId][$student_id]))
				{
					unset($this->testMember[$testId][$student_id]);
				}
			}
			if(empty($this->testMember[$testId]))
			{
				$this->testMember = array();
			}
		}	
	}

	private function uniqueMember($array)
	{	
		$array = json_decode(json_encode($array), true);
		foreach($array as $testID=>$value)
		{
			foreach($value as $studentId=>$v)
			{				
				$array[$testID][$studentId] = array_values($array[$testID][$studentId])[0];
				unset($array[$testID][$studentId]["resourceId"]);
			}		
			$array[$testID] = array_values($array[$testID]);
		}
		$array = json_decode(json_encode($array), false);
		return $array;
	}

	/**
	 * Method when a new client is connected
	 * @param \WSClient $wsClient Chat client
	 */
	public function onOpen($wsClient)
	{
		$this->wsDatabase->connect();
		$sessions = $wsClient->getSessions();
		$query = $wsClient->getQuery();
		$clientData = $wsClient->getClientData();	
		
		if($wsClient->getGroupId() == "student" && @$query['module'] == "test" && !empty(@$query['test_id']))
		{
			if(isset($clientData['username']))
			{
				$username = isset($sessions['student_username'])?$sessions['student_username']:null;
				$password = isset($sessions['student_password'])?$sessions['student_password']:null;
				if(!empty($username))
				{
					$student = $this->wsDatabase->getLoginStudent($username, $password, $wsClient->getResourceId());
					$testId = $query['test_id'];
					$this->memberTestAdd($student, $testId);

					$response = json_encode(
						array(
							'command' => 'log-in', 
							'data' => array(
								array(
									'data'=>$student
								)
							)
						)
					);
					$wsClient->sendMessage($response);
				}
			}

			$this->updateUserOnSystem();
			// Send user list
			$response = json_encode(
				array(
					'command' => 'test-member', 					
					'data' => array(
						array(
							'test_member'=>$this->uniqueMember($this->testMember)
						)
					)
				)
			);
			$this->sendBroadcast($wsClient, $response, array('admin', 'teacher'), false);					
		}
		else if(!empty(@$query['test_id']))
		{
			$response = json_encode(
				array(
					'command' => 'test-member', 
					'group_id' => $wsClient->getClientData()['group_id'],
					'data' => array(
						array(
							'test_member'=>$this->uniqueMember($this->testMember)
						)
					)
				)
			);		
			$wsClient->sendMessage($response);
		}
		$this->wsDatabase->disconnect();
	}
	
	/**
	 * Method when a new client is disconnected
	 * @param $wsClient Chat client
	 */
	public function onClose($wsClient)
	{
		$this->wsDatabase->connect();
		$this->updateUserOnSystem();
		$sessions = $wsClient->getSessions();

		$query = $wsClient->getQuery();

		if($wsClient->getGroupId() == "student" && @$query['module'] == "test" && !empty(@$query['test_id']))
		{			
			$username = isset($sessions['student_username'])?$sessions['student_username']:null;
			$password = isset($sessions['student_username'])?$sessions['student_password']:null;
			$student = $this->wsDatabase->getLoginStudent($username, $password, $wsClient->getResourceId());
			$testId = $query['test_id'];
 			$this->memberTestRemove($student, $testId);

			$this->updateUserOnSystem();
			// Send user list
			$response = json_encode(
				array(
					'command' => 'test-member', 
					'data' => array(
						array(
							'test_member'=>$this->uniqueMember($this->testMember)
						)
					)
				)
			);
		
			$this->sendBroadcast($wsClient, $response, array('admin', 'teacher'), false);
		}		
		$this->wsDatabase->disconnect();
	}

	/**
	 * Method when a client send the message
	 * @param \WSClient $wsClient Chat client
	 * @param string $receivedText Text sent by the client
	 */
	public function onMessage($wsClient, $receivedText)
	{
		$json_message = json_decode($receivedText, true); 
		
		$fp = fopen(dirname(__FILE__)."/log.txt", "a"); fputs($fp, "Client = ".print_r($wsClient->getClientData(), true)."\r\n\r\n Message = '".($receivedText)."'\r\n\r\n\r\n"); fclose($fp);
	
		$command = $json_message['command'];
		
		if($command == "broadcast")
		{
			$receiverGroup = $json_message['receiver_group'];
			$this->sendBroadcast($wsClient, $receivedText, $receiverGroup, true);
		}
		else if($command == "message" || $command == "kick")
		{
			$receiver = $json_message['receiver'];
			if(is_array($receiver))
			{
				$this->sendMessage($receivedText, $receiver);
			}
		}
	}
	

	
}
