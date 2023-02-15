<?php

class MyApp {

   /**
	 * Method when a new client is login
	 * @param \WSClient $clientChat Chat client
	 * @return array
	 */
	public function prostConstructClient($clientChat)
	{
		// Here are the client data
		// You can define it yourself
		$sessions = $clientChat->getSessions();
		if(isset($sessions['student_username']) && !empty($sessions['student_username']))
		{
			$clientData = array(
				'login_time'=>date('Y-m-d H:i:s'), 
				'username'=>$sessions['student_username'], 
				'group_id'=>'student'
			);
		}
		else if(isset($sessions['teacher_username']) && !empty($sessions['teacher_username']))
		{
			$clientData = array(
				'login_time'=>date('Y-m-d H:i:s'), 
				'username'=>$sessions['teacher_username'], 
				'group_id'=>'teacher'
			);
		}
		else if(isset($sessions['admin_username']) && !empty($sessions['admin_username']))
		{
			$clientData = array(
				'login_time'=>date('Y-m-d H:i:s'), 
				'username'=>$sessions['admin_username'], 
				'group_id'=>'admin'
			);
		}
		else
		{
			$clientData = array();
		}
		
		
		return $clientData;
	}

}