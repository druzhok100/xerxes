<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Authentication;

use Application\Model\Authentication;

/**
 * Custom authentication
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Custom extends Authentication\Scheme
{
	public function onLogin()
	{
		// Use this to redirect the user to an exteral system where they will login; 
		// in which case, send the user back to the authenticate/validate action together with 
		// other querystring parameters as needed; that will in turn call onCallBack() below
		
		/* Example:
		
		$url = "https://some.example.edu/login?return=" . $this->validate_url;
		$this->redirectTo($url);
		*/
	}
	
	public function onCallBack()
	{
		
		// LOCAL:
		//
		// if you are using the Xerxes login form for authentication
		// then do your authentication logic here
		
		// the following paramaters are the default ones offered  by the form; if you need others, 
		// simply customize the login form
		
		$strUsername = $this->request->getParam("username");
		$strPassword = $this->request->getParam("password");
		
		// REMOTE:
		//
		// if onLogin() pushed the user to an external system for login, then this function
		// will be called when the user comes back; the task here then is simply to validate 
		// that the login was successful (if necessary), and register the user; If the request
		// is bad, throw an Exception with details, do _not_ simply return self::FAILURE
		

		// REGISTRATION:
		//
		// If the user passed the authentication challenge, then assign values to the properties
		// below; username is REQUIRED, all others optional
		
		$this->user->username;
		$this->user->first_name;
		$this->user->last_name;
		$this->user->email_addr;

		// on success:
		
		// return $this->register(); 
		
		// on failure: 
		
		return self::FAILED;
	}
}
