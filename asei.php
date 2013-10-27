<?php

	/**
	 * ASEI :: Server-Side
	 * Application Server Event Interface
	 *
	 * Handles server-side application events with client-side
	 * event push notifications.
	 *
	 * Notice:
	 * ASEI does not support legacy browsers such as Internet Explorer
	 * Currently Internet Explorer 10 still does not support SSE technology
	 * so working configurations are currently limited to Firefox, Chromium,
	 * Opera, Safari and any other web standards compliant browser.	
	 *
	 *@todo Security and Public Events where events should evaluate that the
	 *requests come from this host unless otherwise specified. It may be neat
	 *to have someone else listing for your events.
	 *
	 *@todo Plugins and Functional code. Some initial start up code to load
	 *server libraries/framework, and then some plugin style dynamic object
	 *manager
	 *
	 *@todo .htaccess security redirects and restrictions
	 *
	 *
	 *@author Geoffrey L. Kuhl
	 *@package asei
	 *@subpackage server-side
	 *@version 0.1.5
 	 */
	

	/**
	 * ASEI
	 * Global Instance
	 * 
	 * @var asei
	 */
	global $asei;
	$asei 		= new asei();



	/**
	 * Functional Wrappers
	 * wraps Object with functional methods
	 */
	

	/**
	 * Register Handler
	 * 
	 * Attach an event handler to event, or Listen for an event.
	 * The handler should be able to be called statically, so the
	 * parent class object does not need to be instansiated.
	 *
	 * <code>
	 * 	// Your Declaration
	 * 	class myObject {
	 * 		public static function myMethod(array $eventData = array()) {}
	 * 	}
	 *
	 *  // Usage
	 * 	registerHandler('some_event_name', myObject', 'myMethod');
	 * </code>
	 * 
	 * @param  string $eventName Name of event to listen for
	 * @param  string $object Name of object class to use
	 * @param  string $method Name of object method to call
	 * @return array Event Handlers
	 */
	function registerHandler($eventName, $object, $method) {
		global $asei;
		return $asei->registerHandler($eventName, $object, $method);
	}

	/**
	 * Raise Event
	 *
	 * Raises an event which calls all listening/registered handlers 
	 * and passes incoming event data to the handler.
	 *
	 * <code>
	 * 	raiseEvent('some_event_name', array('mydata'=>'this'));
	 * </code>
	 * 
	 * @param  string 	$eventName Name of the event to raise
	 * @param  array  	$data Data to pass to handlers
	 * @param  integer  $retry Retry time
	 * @return array 	array('server'=>array(), 'client'=>array());
	 */
	function raiseEvent($eventName, array $data = array(), $retry = false) {
		global $asei;
		return $asei->raiseEvent($eventName, $data, $retry);
	}



	/**
	 * ASEI
	 * Class asei
	 */
	class asei {


		/**
		 * ASEI Version
		 */
		const version 				= "0.1.5";


		/**
		 * Date/Time String Format
		 */
		const dateTime_format		= "l, F jS \o\\f Y \a\\t g:i:s A T";


		/**
		 * Refresh Construct Event Interval in milliseconds
		 * Default is 10000 or 10 Seconds
		 */		
		const refreshMainPoll		= 10000;


		/**
		 * Storage for incoming AJAX requests
		 * @var array
		 */
		protected $postedRequest	= array();


		/**
		 * Storage for Registered event handlers
		 * @var array
		 */
		protected $serverHandlers	= array();


		/**
		 * Storage for event raising logs
		 * @var array
		 */
		private $logs				= array();



		/**
		 * Construct
		 * Checks for incoming AJAX requests
		 */
		function __construct() {
			if (!$this->handleRequest()) {
				$this->raiseEvent('aseiPoll', $this->pollEventData(), self::refreshMainPoll);	
			}
		}



		/**
		 * @todo
		 * @return boolean
		 */
		private function hasDirectContact() {
			$req 	= (substr(__FILE__, -strlen($_SERVER['REQUEST_URI'])));
			$uri 	= $_SERVER['REQUEST_URI'];
			return ($req == $uri);;
		}



		/**
		 * Start-up Event Data
		 * @return array Returns ASEI information
		 */
		private function pollEventData() {
			return array(
				'ASEI'			=> 'By Geoffrey L. Kuhl',
				'version'		=> self::version,
				'repolled'		=> microtime(true),
				'dateTime'		=> date(self::dateTime_format),	
				'uptime'		=> `uptime`		
			);
		}



		/**
		 * Method Call
		 * Calls a method on an object. Attempts to create an instane of the object first
		 * if possible
		 *
		 * @param  string  $objectName
		 * @param  string  $methodName
		 * @param  array   $arguments
		 * @param  boolean $status
		 * @return mixed Return of Call
		 */
		private function methodCall($objectName, $methodName, array $arguments = array(), &$status = true) {

			$object 			= null;
			if (isset($GLOBALS[$objectName]) and method_exists($GLOBALS[$objectName], $methodName)) {
				$object 		= $GLOBALS[$objectName];
			} elseif (class_exists($objectName) and method_exists($objectName, $methodName)) {

				try {
					$object 	= new $objectName();
				} catch (Exception $e) {
					$object 	= $objectName;
				}

			} else {
				$status			= false;
				return "$objectName->$methodName(".implode(',', $arguments).") could not be found or executed";
			}

			try {
				return call_user_func_array(array($object, $methodName), $arguments);
			} catch (Exception $e) {
				$status			= false;
				return $e->getMessage();
			}

		}



		/**
		 * Gather Post
		 * Clone Post data to private variable for later use
		 * 
		 * @return boolean True if post data was retrieved
		 */
		private function gatherPost() {
			if (!count($_POST)) return false;
			$this->postedRequest = $_POST;
			return true;
		}



		/**
		 * Handle Request
		 * Handle incoming POST request (XHR) from
		 * jQuery.post() Call
		 *
		 * @return boolean True if POST data exists
		 */
		private function handleRequest() {
			if (!$this->gatherPost()) return false;
			
			$post 		= $this->postedRequest;
			$objName	= (isset($post['class'])) 	? $post['class'] 	: null;
			$funcName	= (isset($post['method']))	? $post['method']	: null;
			$funcParam	= array();

			if (!$funcName or !$objName) {
				$this->sendJSON('No class or function defined', true);
			} else {
				unset($post['class']);
				unset($post['method']);
				$funcParam	= $post;
			}

			if (!class_exists($objName) or !method_exists($objName, $funcName)) {
				$this->sendJSON("Class '$objName' or function '$funcName' not defined", true);
			} elseif ($objName == 'asei') {
				$this->sendJSON("Cannot make calls to myself", true);
			}

			$status     = true;
			$result		= $this->methodCall($objName, $funcName, $funcParam, $status);
			$this->sendJSON($result, ($status === false));

			return true;
		}



		/**
		 * Send JSON
		 * Prints JSON encoded data for output or for error
		 * 
		 * @param  array $data
		 * @param  boolean $error
		 * @return void
		 */
		private function sendJSON($data, $error = false) {

			/** Attach Error if needed **/
			if ($error) $data = array('error' => $data);

			/** Create Standard Return Struction **/
			$return 	= array(
				'result'	=> $data,
				'request'	=> $this->postedRequest,
				'timestamp'	=> date(self::dateTime_format)
			);

			print json_encode($return);
			if ($error == true) exit();
		}



		/**
		 * Call Handler
		 * Calls all server-side event handlers listening
		 * for given event
		 * 
		 * @param  string $eventName
		 * @param  array  $data
		 * @return array  List of logs from handler calls
		 */
		private function callHandler($eventName, array $data) {

			$handlers 		= (isset($this->serverHandlers[$eventName]))
				? $this->serverHandlers[$eventName]
				: array()
			;

			foreach ($handlers as $handler) {
				$object 	= $handler['object'];
				$method 	= $handler['method'];

				if (!class_exists($object))				continue;
				if (!method_exists($object, $method))	continue;

				/** Log Data **/
				$this->logs['eventName'][]	= "Raised Server Event $object->$method([".count($data)."])";

				$append		= array('eventName' => $eventName);
				$cleanData	= filter(array_merge($data, $apend));
				$return		= call_user_func_array(array($object, $method), $cleanData);
			}

			return (isset($this->logs['eventName'])) ? $this->logs['eventName'] : array();
		}



		/**
		 * Push Event
		 * Sends a stream for client-side event raising
		 * 
		 * @param  string  $eventName
		 * @param  array   $data
		 * @param  integer $retry
		 * @return array 	List of logs from event
		 */
		protected function pushEvent($eventName, $data = array(), $retry = 10000) {
			/** Encode Data **/
			$data 			= json_encode($data);

			/** No retry **/
			if ($retry === false) {
				$retry 		= ((((60 * 60) * 24) * 365) * 1000);
			}

			/** Log Data **/
			$this->logs['eventName'][]	= "Pushed Client Event $eventName([".count($data)."])";
			
			/** Prepare Structure **/
			$strPackage		= "retry: $retry".PHP_EOL;
			$strPackage    .= "event: $eventName".PHP_EOL;
			$strPackage    .= "data: $data".PHP_EOL;

			/** Send **/
			header('Content-Type: text/event-stream');
			header('Cache-Control: no-cache');
			print $strPackage.PHP_EOL;
			flush();

			/** Log Data **/
			$this->logs['eventName'][]	= "Sent:".PHP_EOL.$strPackage;

			return $this->logs['eventName'];
		}



		/**
		 * Register Handler
		 * 
		 * Attach an event handler to event, or Listen for an event.
		 * The handler should be able to be called statically, so the
		 * parent class object does not need to be instansiated.
		 *
		 * <code>
		 * 	// Your Declaration
		 * 	class myObject {
		 * 		public static function myMethod(array $eventData = array()) {}
		 * 	}
		 *
		 *  // Usage
		 * 	registerHandler('some_event_name', myObject', 'myMethod');
		 * </code>
		 * 
		 * @param  string $eventName Name of event to listen for
		 * @param  string $object Name of object class to use
		 * @param  string $method Name of object method to call
		 * @return array Event Handlers
		 */
		public function registerHandler($eventName, $object, $method) {
			$this->serverHandlers[$eventName][]	= array(
				'object'	=> $object,
				'method'	=> $method,
				'event'		=> $eventName
			);

			return $this->serverHandlers[$eventName];
		}



		/**
		 * Raise Event
		 *
		 * Raises an event which calls all listening/registered handlers 
		 * and passes incoming event data to the handler.
		 *
		 * <code>
		 * 	raiseEvent('some_event_name', array('mydata'=>'this'));
		 * </code>
		 * 
		 * @param  string 	$eventName Name of the event to raise
		 * @param  array  	$data Data to pass to handlers
		 * @param  integer  $retry Retry time
		 * @return array 	array('server'=>array(), 'client'=>array());
		 */
		public function raiseEvent($eventName, array $data = array(), $retry = 10000) {
			$serverCalls	= $this->callHandler($eventName, $data);
			$clientCalls	= $this->pushEvent($eventName, $data, $retry);

			return array('server'=>$serverCalls, 'client'=>$clientCalls);
		}

	}


?>