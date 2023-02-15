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
		$clientData = array(
			'login_time'=>date('Y-m-d H:i:s'), 
			'username'=>@$clientChat->getSessions()['planet_username'], 
			'full_name'=>@$clientChat->getSessions()['planet_full_name'],
			'avatar'=>@$clientChat->getSessions()['planet_avatar'],
			'sex'=>@$clientChat->getSessions()['planet_sex']
		);
		
		return $clientData;
	}

}