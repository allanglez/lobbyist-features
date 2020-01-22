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
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Provides a custom block.
 *
 * @Block(
 *   id = "in-house_single_act_block",
 *   admin_label = @Translation("In-House Activity block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class InHouseActivity extends BlockBase implements BlockPluginInterface{
        
    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    } 
    /**
     * {@inheritdoc}
    */
    public function build(){
      $current_user = \Drupal::currentUser();
      $email = $current_user->getEmail();
      $roles = $current_user->getRoles();
      
      $activity_endpoind = \Drupal::config('biz_block_plugin.settings')->get('in_house_activity');
      $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $param = \Drupal::request()->query->all();
      //Get activity ID
      $id = isset($param['id']) ? $param['id'] : "0";
      $activity_response = GeneralFunctions::getSubmission($activity_endpoind, $id);
      $activity_data = json_decode($activity_response['message']);
      $activity_data= $activity_data[0];
      $activity_email = isset($activity_data->mail) ? $activity_data->mail : "";
      $url = $base_url . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . $activity_email;
      $get_organization = BizWebformController::get_endpoint($url, [], "GET", []);
      if($get_organization['code'] !== 400){
        $organization = json_decode($get_organization["message"])[0];
      }
      if(isset($activity_data) && !empty($activity_data)){
	      $base_href = '/in-house-account-home/in-house-add-activity-edit?';
        foreach($activity_data as $field => $value) { 
            $base_href .=  $field . '=' . $value . '&'; 
        }
        $editable = ($email === $activity_email ||  in_array("role_administrator", $roles)) ? TRUE : FALSE;
        
        $edit_activity = "";
        if($editable){
          if(!empty($activity_data)){
            $base_href = '/in-house-account-home/in-house-activity-view/in-house-add-activity-edit?org=' . $id .'&';
            foreach($activity_data as $field => $value) { 
                $base_href .=  $field . '=' . $value . '&'; 
            }
           $edit_activity =  $base_href;
          }
        }
        if($email === $activity_email){
          $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
        }
        $content[] = array( '#theme' => 'in_house_account_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t(' Consultant lobbyist'));   
        $content[] = array( '#theme' => 'in_house_activity', '#activity' => $activity_data, "#link_edit_act" => $edit_activity );
       
        if($editable){
          $url = $base_url . \Drupal::config('biz_block_plugin.settings')->get('get_comments') ."?_format=json&id=" . $id;
          $get_comments = BizWebformController::get_endpoint($url, [], "GET", []);
          
          if($get_comments['code'] !== 400){
              $get_comments = json_decode($get_comments["message"]);
              $title = '<div class="organization info-organization purple-header new-notifications-header">'
              .   '<div class="col-xs-12">'
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
  }