<?php
  namespace Drupal\biz_block_plugin\Plugin\Block;
  
  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Block\BlockPluginInterface;
  use Drupal\Core\Form\FormBuilderInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Access\AccessResult;
  use Drupal\Core\Cache\Cache;
  use Drupal\biz_webforms\BizWebformController;
  use Drupal\biz_block_plugin\Controller\GeneralFunctions;

/**
 * Provides a custom block.
 *
 * @Block(
 *   id = "consultant_single_act_block",
 *   admin_label = @Translation("Consultant Activity block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class ConsultantActivity extends BlockBase implements BlockPluginInterface{
    
    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $current_user = \Drupal::currentUser();
      $email = $current_user->getEmail();
      $roles = $current_user->getRoles();
      $base_url =  \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $edit_activity = "";
      $edit_organization = "";
      $organization = [];
      $activity_data = [];
      $activity_endpoint = \Drupal::config('biz_block_plugin.settings')->get('consultant_activity');
      //Get all query params
      $param = \Drupal::request()->query->all();
      //Get activity ID
      $id = isset($param['id']) ? $param['id'] : "0";
      //Get activity data
      $activity_response = GeneralFunctions::getSubmission($activity_endpoint, $id);
      if($activity_response['code'] !== 400){
        $activity_data = isset(json_decode($activity_response['message'])[0]) ? json_decode($activity_response['message'])[0] : [];
      }      
      $activity_email = isset($activity_data->mail) ? $activity_data->mail : "";
      //Generate the URL for get backend user information
      $url = $base_url . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . $activity_email;
      //Get organization data
      $get_organization = BizWebformController::get_endpoint($url, [], "GET", []);
      if($get_organization['code'] !== 400){
        $organization = isset(json_decode($get_organization['message'])[0]) ? json_decode($get_organization['message'])[0] : [];
      }
      $editable = ($email === $activity_email ||  in_array("role_administrator", $roles)) ? TRUE : FALSE;
      
      //Check if the user is the owner activity
      if($email === $activity_email ){
        $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
      }
      
      
      if(!empty($activity_data) && ($editable)){
        $base_href = '/consultant-account-home/consultant-activity-view/consultant-add-activity-edit?org=' . $id .'&';
        foreach($activity_data as $field => $value) { 
            $base_href .=  $field . '=' . $value . '&'; 
        }
       $edit_activity =  $base_href;
      }
      $content[] = array( '#theme' => 'consultant_account_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t(' Consultant lobbyist'));   
      $content[] = array( '#theme' => 'consultant_activity', '#activity' => $activity_data, "#link_edit_act" => $edit_activity );  
      if($editable){
        $url = $base_url . \Drupal::config('biz_block_plugin.settings')->get('get_comments') ."?_format=json&id=" . $id;
        $get_comments = BizWebformController::get_endpoint($url, [], "GET", []);
        if($get_comments['code'] !== 400){           
            $get_comments = json_decode($get_comments["message"]);
            $title = '<div class="header-view purple-header">'
            .   '<div class="col-xs-12 no-padding">'
            .     '<p><strong>'.t('Your messages').'</strong></p>'
            .   '</div>'
            . '</div>';
            $content[] = array('#type' => 'markup', '#markup' => $title);
            
            
            if(!empty($get_comments) && is_array($get_comments)){
              foreach($get_comments as $key => $comment){
                if(isset($comment->comment_body) && !empty($comment->comment_body)){
                  $content[] = array( '#theme' => 'activity_messages', '#subject' => $comment->subject, "#user_name" => $comment->user_name , "#message" => $comment->comment_body, "#date" =>  $comment->changed);
                }
              }
            }
        }
        $content[] = \Drupal::formBuilder()->getForm("Drupal\biz_activity_messages\Form\ActivityMessages");
      } 
      return $content;
    }
  }