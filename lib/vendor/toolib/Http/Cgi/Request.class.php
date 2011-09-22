<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */

namespace toolib\Http\Cgi;
use toolib\Http\ParameterContainer;

require_once __DIR__ . '/../Request.class.php';

//! Wrapper for CGI Request
/**
 * @brief Implementation of Request for CGI Gateway
 * Manage meta-variables of a CGI Request
 * @property integer $cgi_version The version of cgi protocol.
 * @property array $server_info Information about this server.
 * @property array $remote_info Information about this remote end point.
 * @property string $path_info Part of the path after script name.
 * @property string $script_name The actual script that is executed 
 */
class Request extends \toolib\Http\Request
{
	/**
	 * All the cgi meta-variables
	 * @var array
	 */
	private $_meta_variables = array();
	
	/**
	 * Flag if this is a request wrapping php
	 * @var boolean
	 */
	private $_php_request;

	/**
	 * @param array $meta_variables The meta variables as defined in CGI protocol.
	 * @param boolean $php_request Flag if this instance represents actual php request.
	 *  Enabling it, other superglobal variables will also be used.
	 */
	public function __construct($meta_variables = null, $php_request = false)
	{
		$this->_php_request = $php_request;
		
		if ($meta_variables !== null) {
			$this->_meta_variables = $meta_variables;
			return;
		}
		
		// Create default Request
		$this->_meta_variables = array(
			'SERVER_SOFTWARE' => 'toolib',
			'SERVER_NAME' => 'localhost',
			'GATEWAY_INTERFACE' => 'CGI/1.1',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'SERVER_PORT' => 80,
			'REQUEST_METHOD' => 'GET',
			'PATH_INFO' => null,
			'SCRIPT_NAME' => '',
			'REQUEST_URI' => '/',
			'QUERY_STRING' => null,
			'REMOTE_HOST' => '',
			'REMOTE_ADDR' => '',
			'CONTENT_TYPE' => 'text/html',
			'CONTENT_LENGTH' => null
		);
	}

	
	/**
	 * @brief Create ParameterContainer from the query string
	 * @return \toolib\Http\ParameterContainer
	 */
	private function queryStringToContainer()
	{
		$container = new ParameterContainer();
		if( !isset($this->_meta_variables['QUERY_STRING']))
			return $container;
			
		$chunks = explode('&', $this->_meta_variables['QUERY_STRING']);
		
		foreach ($chunks as $chunk) {
			$parts = explode('=', $chunk);
			$container[urldecode($parts[0])] = urldecode($parts[1]);
		}

		return $container;			
	}
	
	/**
	 * @brief Create an array of Cookie objects
	 */
	private function cookiesToContainer()
	{
		$container = new ParameterContainer();
		if( !isset($this->_meta_variables['HTTP_COOKIE']))
			return $container;
			
		$chunks = explode(';', $this->_meta_variables['HTTP_COOKIE']);		
		foreach($chunks as $chunk) {
			$parts = explode('=', trim($chunk));
			if ($parts[0][0] == '$')
				continue;
			$container[urldecode($parts[0])] = urldecode($parts[1]);
		}
		
		return $container;	
	}
	
	/**
	 * @brief Create an array of Header objects
	 */
	private function headersToContainer()
	{
		$container = new ParameterContainer();
		
		// Loop around meta variables
		foreach($this->_meta_variables as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
				$container[$key] = $value;
			}
		}
		
		return $container;	
	}
	
	/**
	 * @brief Dynamically convert CGI variables to Request
	 * interface.
	 * @param string $property The name of the property.
	 */
	public function __get($property)
	{
		if ($property == 'query') {
			if ($this->_php_request)
				$this->$property = new ParameterContainer($_GET);
			else
				$this->$property = $this->queryStringToContainer();
				
		} else if ($property == 'uri') {
			$this->$property = $this->_meta_variables['REQUEST_URI'];
			
		} else if ($property == 'method') {
			$this->$property = $this->_meta_variables['REQUEST_METHOD'];
			
		} else if ($property == 'http_version') {
			$this->$property = isset($this->_meta_variables['SERVER_PROTOCOL'])?
				substr($this->_meta_variables['SERVER_PROTOCOL'], -3):'1.0';
			
		} else if ($property == 'scheme') {
			$this->$property = ((!isset($this->_meta_variables['HTTPS']))
				|| $this->_meta_variables['HTTPS'] == 'off')?'HTTP': 'HTTPS';
				
		} else if ($property == 'cookies') {
			if ($this->_php_request)
				$this->$property = new ParameterContainer($_COOKIE);
			else
				$this->$property = $this->cookiesToContainer();
				
		} else if ($property == 'headers') {
			if ($this->_php_request && function_exists('apache_request_headers')) 
				$this->$property = new ParameterContainer(apache_request_headers);
			else
				$this->$property = $this->headersToContainer();
				
		} else if ($property == 'raw_content') {
			$this->$property = $this->_php_request ? file_get_contents('php://input'):null;
			
		} else if ($property == 'content') {
			$this->$property = ($this->_php_request)?new ParameterContainer($_POST):null;
			
		} else if ($property == 'cgi_version') {
			$this->$property = isset($this->_meta_variables['GATEWAY_INTERFACE'])
				?substr($this->_meta_variables['GATEWAY_INTERFACE'], -3):'1.1';
				
		} else if ($property == 'server_info') {
			$this->$property = array();
			if (isset($this->_meta_variables['SERVER_ADDR']))
				$this->server_info['addr'] = $this->_meta_variables['SERVER_ADDR'];
			if (isset($this->_meta_variables['SERVER_PORT']))
				$this->server_info['port'] = $this->_meta_variables['SERVER_PORT'];
				if (isset($this->_meta_variables['SERVER_NAME']))
				$this->server_info['name'] = $this->_meta_variables['SERVER_NAME'];
			if (isset($this->_meta_variables['SERVER_SOFTWARE']))
				$this->server_info['software'] = $this->_meta_variables['SERVER_SOFTWARE'];
			if (isset($this->_meta_variables['SERVER_PROTOCOL']))
				$this->server_info['protocol'] = $this->_meta_variables['SERVER_PROTOCOL'];
			
		} else if ($property == 'remote_info') {
			$this->$property = array();
			if (isset($this->_meta_variables['REMOTE_ADDR']))
				$this->remote_info['addr'] = $this->_meta_variables['REMOTE_ADDR'];
			if (isset($this->_meta_variables['REMOTE_PORT']))
				$this->remote_info['port'] = $this->_meta_variables['REMOTE_PORT'];
			if (isset($this->_meta_variables['REMOTE_HOST']))
				$this->remote_info['name'] = $this->_meta_variables['REMOTE_HOST'];

		} else {
			throw new \InvalidArgumentException('Unknown ' . __CLASS__ . "->{$property} property was requested.");
		}
		
		return $this->$property;
	}
	
	/**
	 * @brief The current request object instance.
	 * @var \toolib\Http\Cgi\Request
	 */
	static private $current_instance = null;
	
	/**
	 * @brief Get CGI Instance of the current page request.
	 * @return \toolib\Http\Cgi\Request
	 */
	public static function getCurrent()
	{
		if (self::$current_instance !== null)
			return self::$current_instance;
			
		return self::$current_instance = new static($_SERVER, true);
	}
}