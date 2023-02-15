<?php

class WSBrokerService extends WSServer implements WSInterface {
	public function __construct($wsDatabase, $host = '127.0.0.1', $port = 8888, $callbackObject = null, $callbackPostConstruct = null)
	{
		parent::__construct($wsDatabase, $host, $port, $callbackObject, $callbackPostConstruct);
	}
	public function updateUserOnSystem()
	{
		$this->resetUserOnSystem();
		foreach($this->chatClients as $client)
		{
			if(isset($client->getClientData()['username']))
			{
				$this->setUserOnSystem($client->getClientData()['username'], $client->getClientData());
			}
		}
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
	private function memberTestRemove($student, $testId)
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

	private $testMember = array();

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
		

		if($clientData['group_id'] == "student" && @$query['module'] == "test" && !empty(@$query['test_id']))
		{
			if(isset($clientData['username']))
			{
				$username = isset($sessions['student_username'])?$sessions['student_username']:null;
				$password = isset($sessions['student_username'])?$sessions['student_password']:null;
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
					$wsClient->send($response);
				}
			}

			$this->updateUserOnSystem();
			// Send user list
			$response = json_encode(
				array(
					'command' => 'user-on-system', 
					
					'data' => array(
						array(
							'test_member'=>$this->uniqueMember($this->testMember)
						)
					)
				)
			);

			$this->sendBroadcast($wsClient, $response, array('admin', 'teacher'), false);
			
			
		}
		if(@$clientData['group_id'] == "admin" && !empty(@$query['test_id']))
		{
			$response = json_encode(
				array(
					'command' => 'user-on-system', 
					'group_id' => $wsClient->getClientData()['group_id'],
					'data' => array(
						array(
							'test_member'=>$this->uniqueMember($this->testMember)
						)
					)
				)
			);
			
			$wsClient->send($response);
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

		if(@$query['group_id'] == "student" && @$query['module'] == "test" && !empty(@$query['test_id']))
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
					 'command' => 'user-on-system', 
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
		
		if(isset($json_message['command']))
		{
			$command = $json_message['command'];
			$unique_id = uniqid();
			$json_message['data'][0]['read'] = false;
			$json_message['data'][0]['unique_id'] = $unique_id;
			$json_message['data'][0]['timestamp'] = round(microtime(true)*1000);
			$json_message['data'][0]['date_time'] = date('j F Y H:i:s');
			$json_message['data'][0]['sender_name'] = $wsClient->getClientData()['full_name'];
			$json_message['data'][0]['sender_id'] = $wsClient->getClientData()['username'];
			
			if(isset($json_message['data'][0]['receiver_id']))
			{
				$receiver_id = $json_message['data'][0]['receiver_id'];
				$json_message['data'][0]['partner_id'] = $receiver_id;
				if(isset($this->userOnSystem[$receiver_id]))
				{
					if(isset($this->userOnSystem[$receiver_id]['full_name']))
					{
						$receiver_name = @$this->userOnSystem[$receiver_id]['full_name'];
						$json_message['data'][0]['receiver_name'] = $receiver_name;
					}
				}
			}
			
			if($command == 'send-message')
			{
				$this->processTextMessage($wsClient, $json_message);
			}
			else if($command == 'load-message')
			{
				$this->loadMessage($wsClient, $json_message); 
			}
			else if($command == 'mark-message')
			{
				$this->markMessage($wsClient, $json_message); 
			}
			else if($command == 'delete-message-for-all')
			{
				$this->deleteMessageForAll($wsClient, $json_message); 
			}
			else if($command == 'delete-message')
			{
				$this->deleteMessage($wsClient, $json_message); 
			}
			else if($command == 'clear-message')
			{
				$this->clearMessage($wsClient, $json_message); 
			}
			else if($command == 'video-call')
			{
				$this->videoCall($wsClient, $json_message); 
			}
			else if($command == 'voice-call')
			{
				$this->voiceCall($wsClient, $json_message); 
			}
			else if($command == 'on-call')
			{
				$this->onCall($wsClient, $json_message); 
			}
			else if($command == 'missed-call')
			{
				$this->missedCall($wsClient, $json_message); 
			}
			else if($command == 'reject-call')
			{
				$this->rejectCall($wsClient, $json_message); 
			}
			else if($command == 'client-call' || $command == 'client-accept' || $command == 'client-answer' || $command == 'client-offer' || $command == 'client-candidate')
			{
				$this->forwardWebRTCInfo($wsClient, $json_message); 
			}
			else if($command == 'receive-webrtc-info')
			{
				$this->forwardWebRTCInfo($wsClient, $json_message); 
			}
			else if($command == 'check-user-on-system')
			{
				$this->checkUserOnSystem($wsClient, $json_message);
			}

			else if($command == 'broadcast')
			{
				$this->sendBroadcast($wsClient, $json_message);
			}
		}			
	}
	public function voiceCall($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];

		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver)
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	public function videoCall($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];

		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver)
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	public function onCall($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];

		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver || $client->getClientData()['username'] == $sender_id)
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	public function missedCall($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];

		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver || $client->getClientData()['username'] == $sender_id)
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	public function rejectCall($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];

		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver || $client->getClientData()['username'] == $sender_id)
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	public function loadMessage($wsClient, $json_message)
	{
		// TODO Add your code
	}
	public function clearMessage($wsClient, $json_message)
	{
	}
	public function deleteMessageForAll($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		if($my_id)
		{
			if(isset($json_message['data']))
			{
				$data_all = $json_message['data'];
				foreach($data_all as $data)
				{
					if(isset($data['message_list']))
					{
						$partner_id = $data['receiver_id'];
						if(isset($this->userOnSystem[$partner_id]))
						{
							$partner_data = $this->userOnSystem[$partner_id];
							if(isset($data['message_list']))
							{
								$message_list = $data['message_list'];
								if(is_array($message_list))
								{
									if(count($message_list) > 0)
									{
										$reedback_message = array(
											'command'=>'delete-message-for-all',
											'data'=>array(
												array(
													'partner_id'=>$my_id,
													'flag'=>'read',
													'message_list'=>$message_list
												)
											)
										);

										foreach($this->chatClients as $client) 
										{
											$current_user_data = $client->getClientData();
											$member_id = $current_user_data['username'];
											if($partner_id == $member_id)
											{
												$reedback_message['data'][0]['partner_id'] = $my_id;
												$client->send(json_encode($reedback_message));
											}
											else if($member_id == $my_id) 
											{
												$reedback_message['data'][0]['partner_id'] = $partner_id;
												$client->send(json_encode($reedback_message));
											}
										}
									}
								}
							}
						}
						else
						{
							$partner_data = $this->userOnSystem[$partner_id];
						}
						if(isset($data['message_list']))
						{
							$message_list = $data['message_list'];
							if(is_array($message_list))
							{
								if(count($message_list) > 0)
								{
								}
							}
						}
					}
				}
			}
		}
	}
	public function markMessage($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		if($my_id)
		{
			if(isset($json_message['data']))
			{
				$data_all = $json_message['data'];
				foreach($data_all as $data)
				{
					if(isset($data['message_list']))
					{
						$partner_id = $data['receiver_id'];
						if(isset($this->userOnSystem[$partner_id]))
						{
							$partner_data = $this->userOnSystem[$partner_id];
							if(isset($data['message_list']))
							{
								$message_list = $data['message_list'];
								if(is_array($message_list))
								{
									if(count($message_list) > 0)
									{
										$reedback_message = array(
											'command'=>'mark-message',
											'data'=>array(
												array(
													'partner_id'=>$my_id,
													'flag'=>'read',
													'message_list'=>$message_list
												)
											)
										);
										foreach($this->chatClients as $client) 
										{
											$current_user_data = $client->getClientData();
											$member_id = $current_user_data['username'];
											if($partner_id == $member_id)
											{
												$reedback_message['data'][0]['partner_id'] = $my_id;
												$client->send(json_encode($reedback_message));
											}
											else if($member_id == $my_id) 
											{
												$reedback_message['data'][0]['partner_id'] = $partner_id;
												$client->send(json_encode($reedback_message));
											}
										}
									}
								}
							}
						}
						else
						{
							$partner_data = $this->userOnSystem[$partner_id];
						}
						if(isset($data['message_list']))
						{
							$message_list = $data['message_list'];
							if(is_array($message_list))
							{
								if(count($message_list) > 0)
								{
								}
							}
						}
					}
				}
			}
		}
	}

	public function deleteMessage($wsClient, $json_message)
	{
		$my_id = @$wsClient->getClientData()['username'];
		if($my_id)
		{
			if(isset($json_message['data']))
			{
				$data_all = $json_message['data'];
				foreach($data_all as $data)
				{
					if(isset($data['message_list']))
					{
						$partner_id = $data['receiver_id'];
						if(isset($data['message_list']))
						{
							$message_list = $data['message_list'];
							if(is_array($message_list))
							{
							}
						}
						
						if(isset($this->userOnSystem[$partner_id]))
						{
							$partner_data = $this->userOnSystem[$partner_id];
							if(isset($data['message_list']))
							{
								$message_list = $data['message_list'];
								if(is_array($message_list))
								{
									if(count($message_list) > 0)
									{
										$reedback_message = array(
											'command'=>'delete-message-for-all',
											'data'=>array(
												array(
													'partner_id'=>$partner_id,
													'flag'=>'read',
													'message_list'=>$message_list
												)
											)
										);
	
										foreach($this->chatClients as $client) 
										{
											$current_user_data = $client->getClientData();
											$member_id = $current_user_data['username'];
											if($member_id == $my_id) 
											{
												$client->send(json_encode($reedback_message));
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
		
	public function checkUserOnSystem($wsClient, $json_message)
	{
		$username = $json_message['data'][0]['username'];
		if(isset($this->userOnSystem[$username]))
		{
			$message = json_encode(
				array(
					'command'=>'check-user-on-system',
					'data'=>array(
						array(
							'username'=>$username,
							'available'=>false
							)
						)
					)
			);
		}
		else
		{
			$message = json_encode(
				array(
					'command'=>'check-user-on-system',
					'data'=>array(
						array(
							'username'=>$username,
							'available'=>true
							)
						)
					)
			);
		}
		$wsClient->send($message);
	}
	public function processTextMessage($wsClient, $json_message)
	{
		$sender_id = $wsClient->getClientData()['username'];
		$sender_name = $wsClient->getClientData()['full_name'];
		
		$receiver = $json_message['data'][0]['receiver_id'];
		$receiver_name = @$this->userOnSystem[$receiver]['full_name'];


		$json_message['data'][0]['partner_id'] = $sender_id;
		$json_message['data'][0]['partner_name'] = $wsClient->getClientData()['full_name'];
		$json_message['data'][0]['partner_uri'] = $wsClient->getClientData()['username'];
		$json_message['data'][0]['avatar'] = $wsClient->getClientData()['avatar'];
		
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver)
			{
				$client->send(json_encode($json_message));
			}
		}
		$json_message['data'][0]['partner_id'] = $receiver;
		$json_message['data'][0]['partner_name'] = @$this->userOnSystem[$receiver]['full_name'];
		$json_message['data'][0]['partner_uri'] = @$this->userOnSystem[$receiver]['username'];
		$json_message['data'][0]['avatar'] = @$this->userOnSystem[$receiver]['avatar'];
		foreach($this->chatClients as $client)
		{

			if($client->getClientData()['username'] == $wsClient->getClientData()['username'])
			{
				$client->send(json_encode($json_message));
			}
		}
	}
	
	public function forwardWebRTCInfo($wsClient, $json_message)
	{
		$receiver = $json_message['data'][0]['receiver_id'];
		foreach($this->chatClients as $client)
		{
			if($client->getClientData()['username'] == $receiver)
			{
				$client->send(json_encode($json_message));
			}
		}
	}

}
