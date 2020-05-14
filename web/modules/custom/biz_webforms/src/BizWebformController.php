<?php
namespace Drupal\biz_webforms;

use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\external_db_login\ExternalDBLoginService;


/**
* 
*/
class BizWebformController {

  /**
   * Execute the api
   */
  static function get_endpoint($url, $data = array(), $method, $headers = array(), $authentication = TRUE ) {

    $user = \Drupal::currentUser();
    $user_entity = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($user->id());
    $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
    $response = \Drupal::httpClient()->get(($base_url . '/rest/session/token'));
  	$csrf = (string) $response->getBody();

    $auth_value = $user_entity->get('field_token_auth_')->getString();
    $headers= [
	      'Content-Type' => 'application/json',
	      'X-CSRF-Token' => $csrf 
	      ];
    //Creating a httpClient Object.
    $client = \Drupal::httpClient();
    try {
      $request_options = [];
     

      if(!empty($headers)){
        $request_options['headers'] =  $headers;   
      }
      if(!empty($auth_value) && ($method == 'DELETE'|| $method == 'POST'|| $method == 'PATCH') && $authentication){
        $request_options['auth'] = [$user->getUsername(), (new self)->encrypt_decrypt('decrypt', $auth_value) ];
        //$auth = 'Basic '. base64_encode ($user->getUsername() . ':' . $auth_value);
        //$request_options['headers']['Authorization'] = $auth;
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
        \Drupal::logger('BizWebformController')->error(json_encode($e->getResponse()->getBody()));
        return ["code" => 400, "message" => $response];
      }
    }
  }
    /**
      * Execute external api(backend)
      */    
    static function execute_external_api($url, $data = array(), $method, $request_options ) {
        //Creating a httpClient Object.
        $client = \Drupal::httpClient();
        try {
          if(!empty($data)){
            $request_options['json'] = $data;
          }
          
          $response = $client->$method($url, $request_options);
          return ["code" => $response->getStatusCode(), "message" => $response->getBody()->getContents()];
        }
        catch (\Exception $e) {
         if ($e->hasResponse()) {
            $response = $e->getResponse()->getBody();
            \Drupal::logger('BizWebformController')->error($url);
            \Drupal::logger('BizWebformController')->error(json_encode($e->getResponse()->getBody()));
            return ["code" => 400, "message" => $response];
          }
        }
    }
    
    /*
     * Get the options for executing an external API
     */
    static function get_request_options($auth = FALSE){
        $user = \Drupal::currentUser();
        if(!empty($user->id())){
            $user_entity = \Drupal::entityTypeManager()->getStorage('user')->load($user->id());
            $auth_value = $user_entity->get('field_token_auth_')->getString();
        }
        $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $response = \Drupal::httpClient()->get(($base_url . '/rest/session/token'));
      	$csrf = (string) $response->getBody();
        $request_options['headers']= [
	        'Content-Type' => 'application/json',
            'X-CSRF-Token' => $csrf 
	    ];
	    if($auth && !empty($auth_value)){
    	    $request_options['auth'] = [$user->getUsername(), (new self)->encrypt_decrypt('decrypt', $auth_value) ];
	    }
        return $request_options;
    }
	
	/*
     * Function for update user information (only for system use)
     */	
	static function update_tokens($user_id, $user_fields ){
        $user_entity = \Drupal::entityTypeManager()
            ->getStorage('user')
            ->load($user_id);
        foreach($user_fields as $field => $value){
          $user_entity->set($field, $value);
        }
        $user_entity->set('field_updated_by_system', TRUE);
        $user_entity->save();
	}
    
    /*
     * Function encrypt and decrypt password
     */
    static function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'This is my secret key';
        $secret_iv = 'This is my secret iv';
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    
    /*
     * Redirect to correct homepage depends on the user role
     */
    static function user_redirect($account, $tab = ''){
        $roles = $account->getRoles();
        switch(TRUE){
            case in_array('in_house_lobbyist', $roles):
                (new self)->redirect_to("/in-house-account-home". $tab);
            break;
            case in_array('consultant_lobbyist', $roles):
                (new self)->redirect_to("/consultant-account-home". $tab);
            break;
            case in_array('role_administrator', $roles):
                (new self)->redirect_to("/admin-dashboard". $tab);
            break;
        }
    }
    /*
     * Function redirect to specific path
     */
    static function redirect_to($path) { 
	    $response = new RedirectResponse($path);
        $response->send();
        return;
	}
	
	static function get_comments_array($id){
    	$base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
    	$url = $base_url . \Drupal::config('biz_block_plugin.settings')->get('get_comments') ."?_format=json&id=" . $id;
        $request_options = (new self)->get_request_options(TRUE);
        $get_comments = (new self)->execute_external_api($url, [], 'GET', $request_options);
        $messages = [];
        if($get_comments['code'] !== 400){
                $get_comments = json_decode($get_comments["message"]);
                $title = '<div class="organization info-organization purple-header new-notifications-header">'
                .     '<div class="col-xs-12">'
                .         '<p><strong>'.t('Your messages').'</strong></p>'
                .     '</div>'
                . '</div>';
                $messages[] = array('#type' => 'markup', '#markup' => $title);
                if(!empty($get_comments) && is_array($get_comments)){
                    foreach($get_comments as $key => $comment){
                        if(isset($comment->comment_body) && !empty($comment->comment_body)){
                            $messages[] = array( '#theme' => 'activity_messages', '#subject' => $comment->subject, "#user_name" => $comment->user_name , "#message" => $comment->comment_body, "#date" =>    $comment->changed);
                        }
                    }
                }
        }
        else{
            \Drupal::logger('BizWebformController')->error('Comments could not be obtained: '. $get_comments["message"]);
            return FALSE;
        }
        return $messages;
	}
	
	static function webforms_patching($webform_id, $submission_id, $data, $user_patch = NULL){
    	$current_user = empty($user_patch) ?  \Drupal::currentUser() : $user_patch;
        $email = $current_user->getEmail();
        $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $endpoint = \Drupal::config('biz_lobbyist_registration.settings')->get('json_path');
        $url = $base_url . 'webform_rest/' . $webform_id . '/submission/' . $submission_id;
        $url_user = $base_url .$endpoint . \Drupal::currentUser()->getEmail();

        $request_options = (new self)->get_request_options(TRUE);
        $user = (new self)->execute_external_api($url_user, [], 'GET', $request_options);
         \Drupal::logger('BizWebformController')->notice(json_encode($user));
        if($user['code'] == 400){
            \Drupal::logger('BizWebformController')->error('Patch:' . json_encode($user));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            (new self)->user_redirect(\Drupal::currentUser());
        }
        $user = isset(json_decode($user['message'])[0]) ? json_decode($user['message'])[0] : [];
        if(empty($user_patch)){
            $data['uid']= $user->uid;
        }
        $data['user_uid']= $user->uid;

        $patch_activity = (new self)->execute_external_api($url, $data, 'PATCH', $request_options);
        \Drupal::logger('BizWebformController')->notice(json_encode($data));
        if($patch_activity['code'] == 400){
            \Drupal::logger('BizWebformController')->error(json_encode($patch_activity));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
        }
        else{
            drupal_set_message(t('The information has been updated.'), 'status');
        }

	}
	static function webforms_patching_from_js($webform_id, $submission_id, $data){
    	if(!is_array($data)){
        	$data = json_decode($data, true);
    	}
    	$user = \Drupal\user\Entity\User::load(json_encode( $data['user_uid']));
        (new self)->webforms_patching($webform_id, $submission_id, $data, $user);
        return new ModifiedResourceResponse();
    }
}
