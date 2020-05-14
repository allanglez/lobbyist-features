<?php

namespace Drupal\biz_block_plugin\Controller;

use Drupal\biz_webforms\BizWebformController;
use Drupal\Component\Render\FormattableMarkup; 
use Drupal\Core\Render\Markup;

/****
    * Functions for display activities
    */
class GeneralFunctions{

    /****
        *  Get specific information using user email
        */
    public static function getAllData($endpoint, $basic_auth = FALSE){
        $email = \Drupal::currentUser()->getEmail();
        $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        if(!empty($url_base) && !empty($endpoint) && !empty($email)){
            $url = $url_base . $endpoint    . "?_format=json&email=".$email ;
            $data = [];
            $headers = [];
            $method = "GET";           
            $request_options = BizWebformController::get_request_options($basic_auth);
            $response = BizWebformController::execute_external_api($url, $data, "GET", $request_options);
            if($response['code'] !== "400"){
                return $response;
            }
            \Drupal::logger('GeneralFunctions')->error($endpoint . ': ' . json_encode($response['message']));
            return FALSE;
        }
    }

    /****
        * Get specific submission by ID
        */
    public static function getSubmission($endpoint, $id, $basic_auth = FALSE){
        $email = \Drupal::currentUser()->getEmail();
        $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        if(!empty($url_base) && !empty($endpoint) && !empty($id)){
            $url = $url_base . $endpoint    . "?_format=json&id=".$id ;
            $request_options = BizWebformController::get_request_options($basic_auth);
            $response = BizWebformController::execute_external_api($url, [], "GET", $request_options);
            if(isset($response['code']) && $response['code'] !== "400"){
                return $response;
            }
            \Drupal::logger('GeneralFunctions')->error($endpoint . ': ' . json_encode($response['message']));
            return FALSE;
        }
    }

    /****
        * Getting titles for table headers and name of fields
        */
    public static function getHeadersTable($endpoint, $edit = TRUE){
        $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        if(!empty($url_base) && !empty($endpoint)){
            $url = $url_base . $endpoint    . '?_format=json' ;
            $request_options = BizWebformController::get_request_options(FALSE);
            $response = BizWebformController::execute_external_api($url, [], "GET", $request_options);
            if($response['code'] !== "400"){
                $taxonony= json_decode($response['message']);
                if(is_array($taxonony)){
                    $count = 0;
                    foreach($taxonony as $taxonony_value){
                        $taxonony_value->title = $taxonony_value->title == "empty" ? "" : $taxonony_value->title;                            
                        $field = trim(strip_tags($taxonony_value->field));
                        if($field !== 'actions' || ($edit &&    $field === 'actions') ){
                            $header [$count]['data']= trim(strip_tags($taxonony_value->title));
                            $header[$count]['field'] = $field;
                            $fields[] = trim(strip_tags($taxonony_value->field));
                        }
                        $count++;
                    }
                    return array('header' => $header, 'fields' =>$fields);
                }
            }
            \Drupal::logger('GeneralFunctions')->error($endpoint . ': ' . json_encode($response['message']));
        }
        else{
            \Drupal::logger('GeneralFunctions')->error('Check the Lobbyist configuration: admin/config/biz_lobbyist_registration');
        }
        return FALSE; 
    }

    /****
        * Create the structure for generating tables
        */
    public static function generateTableRows($header_info, $rows){
        $activity_rows = [];
        if(is_array($rows) && !empty($rows)){
            foreach($rows as $row){
             $activity_row = new \StdClass();
                foreach($header_info['fields'] as $field){
                    $row->{$field} = isset($row->{$field}) ? $row->{$field} : "" ;
                    if(strpos($row->{$field}, "</a>") === FALSE && strpos($row->{$field}, "</div>") === FALSE){
                        $activity_row->{$field} = $row->{$field};
                    }else{
                        $activity_row->{$field} = Markup::create($row->{$field}) ;
                    }
                }
                $activity_rows[] = array ('data' => $activity_row);
            }
        }
        return $activity_rows;                
    }

}