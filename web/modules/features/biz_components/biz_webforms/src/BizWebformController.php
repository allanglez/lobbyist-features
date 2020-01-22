<?php
namespace Drupal\biz_webforms;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\ConnectionNotDefinedException;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\external_db_login\ExternalDBLoginService;


/**
* 
*/
class BizWebformController {
  
  /**
   * Execute the api
   */
  static function get_endpoint($url, $data = array(), $method, $headers = array() ) {

    if($method !== "GET" && empty($headers)){
      $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $response = \Drupal::httpClient()->get(($base_url . '/rest/session/token'));
      $csrf = (string) $response->getBody();
      $headers =  array('X-CSRF-Token' => $csrf, "Content-Type" =>"application/json");
    }
    
    //Creating a httpClient Object.
    $client = \Drupal::httpClient();
    try {
      $request_options = [];
      if(!empty($headers)){
        $request_options =  array('headers' => $headers);   
      }
      if(!empty($data)){
        $request_options['json'] = $data;
      }
      $response = $client->$method($url, $request_options);
      return ["code" => $response->getStatusCode(), "message" => $response->getBody()->getContents()];
    }
    catch (\Exception $e) {
     if ($e->hasResponse()) {
        $response = $e->getResponse()->getBody();
        return ["code" => 400, "message" => $response];
      }
    }
  }
  
  static function patch_endpoint($url, $data = array()) {
    //Creating a httpClient Object.
    $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
    $response = \Drupal::httpClient()->get(($base_url . '/rest/session/token'));
  	$csrf = (string) $response->getBody();
    $client = \Drupal::httpClient();
    try {
      $serialized_entity = json_encode($data);
      $response = $client->patch($url, [
	    'auth' => ['klausi', 'secret'],
	    'body' => $serialized_entity,
	    'headers' => [
	      'Content-Type' => 'application/json',
	      'X-CSRF-Token' => $csrf
	    ],
	  ]);
      return ["code" => $response->getStatusCode(), "message" => $response->getBody()->getContents()];
      
    }
    catch (\Exception $e) {
     if ($e->hasResponse()) {
        $response = $e->getResponse();
        return ["code" => 400, "message" => $response];
      }
    }
  }

  
  static function createConnection() {
	  // Set data in $info array.
	  $database = \Drupal::config('external_db_login.settings')->get('external_db_login_database');
	  $username = \Drupal::config('external_db_login.settings')->get('external_db_login_username');
	  $password = \Drupal::config('external_db_login.settings')->get('external_db_login_password');
	  $host = \Drupal::config('external_db_login.settings')->get('external_db_login_host');
	  $port = \Drupal::config('external_db_login.settings')->get('external_db_login_port');
	  $driver = \Drupal::config('external_db_login.settings')->get('external_db_login_driver');
	
	  $info = array(
	    'database' => $database,
	    'username' => $username,
	    'password' => $password,
	    'prefix' => '',
	    'host' => $host,
	    'port' => $port,
	    'driver' => $driver
	  );
	  // Add connection with new database setting.
	  Database::addConnectionInfo('external_db_login_connection', 'default', $info);
	  try {
	    // Active new connection.
	    Database::setActiveConnection('external_db_login_connection');
	  }
	  catch (ConnectionNotDefinedException $e) {
	    // Active default connection if new connection is not stablished.
	    Database::setActiveConnection('default');
	    watchdog_exception('external_db_login', $e);
	  }
	}

	
	static function biz_lobbyist_registration_user_login($account) {
	  $roles = $account->getRoles();
	  if (in_array("in_house_lobbyist", $roles)) {
	    biz_lobbyist_registration_goto("/in-house-account-home");
	  } elseif (in_array("consultant_lobbyist", $roles)) {
	    biz_lobbyist_registration_goto("/consultant-account-home");
	  }elseif(in_array("role_administrator", $roles)){
      biz_lobbyist_registration_goto("/commissioner");
    }
	}
	
	function biz_lobbyist_registration_goto($path) { 
	  $response = new RedirectResponse($path);
	  $response->send();
	  return;
	}
}
