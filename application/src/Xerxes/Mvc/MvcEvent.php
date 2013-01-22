<?php

namespace Xerxes\Mvc;

use Symfony\Component\HttpFoundation,
	Symfony\Component\HttpFoundation\Session\Session,
	Xerxes\Utility\Registry;

/**
 * MVC Event
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @version
 * @package  Xerxes
 * @link
 * @license
 */

class MvcEvent
{
	/**
	 * @var Bootstrap
	 */
	protected $bootstrap;
	
	/**
	 * @var Registry
	 */
	protected $registry;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var Response
	 */
	protected $response;
	
	/**
	 * @var ControllerMap
	 */
	protected $controller_map;
	
	/**
	 * Create a new Mvc Event
	 */
	
	public function __construct(array $config)
	{
		// framework config
		
		$this->bootstrap = new Bootstrap($config); 
		$app_dir = $this->bootstrap->get('application_dir', true);
		
		// application config
		
		$this->registry = Registry::getInstance(); 
		
		// incoming request
		
		$this->controller_map = new ControllerMap($app_dir); 
		$this->request = Request::createFromGlobals($this->controller_map); 
		
		// outgoing response
		
		$this->response = new Response(); 
		$this->response->setViewDirectory("$app_dir/views");
	}
	
	/**
	 * Convenience function to return properties
	 * 
	 * @param string $name
	 */
	
	public function __get($name)
	{
		if ( property_exists($this, $name) )
		{
			return $this->$name;
		}
	}
	
	/**
	 * @return Bootstrap
	 */
	
	public function getBootstrap()
	{
		return $this->bootstrap;
	}
	
	/**
	 * @param Bootstrap $bootstrap
	 */
	
	public function setBootstrap($bootstrap) 
	{
		$this->bootstrap = $bootstrap;
	}	
	
	/**
	 * @return Registry
	 */
	
	public function getRegistry() 
	{
		return $this->registry;
	}

	/**
	 * @return Request
	 */
	
	public function getRequest() 
	{
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function getResponse() 
	{
		return $this->response;
	}

	/**
	 * @return ControllerMap
	 */
	public function getControllerMap() 
	{
		return $this->controller_map;
	}

	/**
	 * @param Registry $registry
	 */
	public function setRegistry(Registry $registry) 
	{
		$this->registry = $registry;
	}

	/**
	 * @param HttpFoundation\Request $request
	 */
	public function setRequest(HttpFoundation\Request $request) 
	{
		$this->request = $request;
	}

	/**
	 * @param HttpFoundation\Response $response
	 */
	public function setResponse(HttpFoundation\Response $response) 
	{
		$this->response = $response;
	}

	/**
	 * @param ControllerMap $controller_map
	 */
	public function setControllerMap(ControllerMap $controller_map)
	{
		$this->controller_map = $controller_map;
	}
}