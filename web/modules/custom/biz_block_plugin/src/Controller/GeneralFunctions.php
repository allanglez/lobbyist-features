<?php
  namespace Drupal\biz_block_plugin\Controller;
  use Drupal\biz_webforms\BizWebformController;
  use Drupal\Component\Render\FormattableMarkup; 
  use Drupal\Core\Render\Markup;
  /**
  *
  */
  class GeneralFunctions{

  	 public static function getAllData($endpoind){
      $email = \Drupal::currentUser()->getEmail();
      $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      if(!empty($url_base) && !empty($endpoind) && !empty($email)){
        $url = $url_base . $endpoind  . "?_format=json&email=".$email ;
        $data = [];
        $headers = [];
        $method = "GET";
        $response = BizWebformController::get_endpoint($url, $data, "GET", [] );
        if($response['code'] !== "400"){
          return $response;
        }
        return FALSE;
      }
    }
    public static function getSubmission($endpoind, $id){
      $email = \Drupal::currentUser()->getEmail();
      $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      if(!empty($url_base) && !empty($endpoind) && !empty($id)){
        $url = $url_base . $endpoind  . "?_format=json&id=".$id ;
        $response = BizWebformController::get_endpoint($url, [], "GET", [] );

        \Drupal::logger('getSubmission')->notice($url);
        if(isset($response['code']) && $response['code'] !== "400"){
          return $response;
        }
        return FALSE;
      }
    }
    /*
     *
    */
    public static function getHeadersTable($endpoind, $edit = TRUE){
      $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      if(!empty($url_base) && !empty($endpoind)){
        $url = $url_base . $endpoind  . '?_format=json' ;
        $data = [];
        $headers = [];
        $method = "GET";
        $response = BizWebformController::get_endpoint($url, $data, "GET", [] );
        if($response['code'] !== "400"){
          $taxonony= json_decode($response['message']);
          if(is_array($taxonony)){
            $count = 0;
            foreach($taxonony as $taxonony_value){
              $taxonony_value->title = $taxonony_value->title == "empty" ? "" : $taxonony_value->title;              
              $field = trim(strip_tags($taxonony_value->field));
              if($field !== 'actions' || ($edit &&  $field === 'actions') ){
                $header [$count]['data']= trim(strip_tags($taxonony_value->title));
                $header[$count]['field'] = $field;
                $fields[] = trim(strip_tags($taxonony_value->field));
              }
              $count++;
            }
            return array('header' => $header, 'fields' =>$fields);
          }
        }
      }
      return FALSE; 
    }
    
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
  