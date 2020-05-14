<?php
namespace Drupal\biz_block_plugin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
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

    /**
     * Display all activity information
    */
    public function build(){
        $validate = \Drupal::config('biz_business_rules.settings')->get('wave_for_not_validate');
        $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $current_user = \Drupal::currentUser();        
        $email = !empty($current_user->getEmail()) ? $current_user->getEmail(): 'anonymous';
        $roles = $current_user->getRoles();
        $param = \Drupal::request()->query->all();
        $is_commissioner = in_array("role_administrator", $roles);
        $today = date("Y-m-d"); // Today
        $current_month = date('m');
        $update_status = "";
        $certify = "";
        $edit_activity = "";
        $edit_organization = "";
        $content = [];
        if ($current_month == '12') {
                $nextYear = date('Y', strtotime('+1 year'));
                $from = date(date('Y') . '-12-31');
                $to = date($nextYear . '-01-31');
        }
        elseif ($current_month >= 1 && $current_month <= 11 ){
                $prevYear = date('Y', strtotime('-1 year'));
                $from = date($prevYear . '-12-31');
                $to = date(date('Y'). '-01-31');
        }
        //Get activity ID
        $id = isset($param['id']) ? $param['id'] : "0";
        $is_owner = FALSE;

        $request_options = BizWebformController::get_request_options(FALSE);
        $url_get_endpoint = $base_url. 'api/activity/'.$id.'/add_a_lobbying_activity/' . $email . '?_format=json' ;
        $activity_endpoint = BizWebformController::execute_external_api($url_get_endpoint, [], "GET", $request_options);
        if($activity_endpoint['code'] !== 400){
            $activity_endpoint['message'] = json_decode($activity_endpoint['message']);
            $is_owner = $activity_endpoint['message']->owner;          
            $auth = $is_commissioner || $is_owner ? TRUE : FALSE;
            $request_options = BizWebformController::get_request_options($auth);
            $activity_endpoint = $activity_endpoint['message']->endpoint;
        }
        else{
            \Drupal::logger('InHouseActivity')->error(json_encode($activity_endpoint));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }
        //Get activity data   
        $activity_response = GeneralFunctions::getSubmission($activity_endpoint, $id, $auth);
        if($activity_response['code'] == 400){
            \Drupal::logger('InHouseActivity')->error(json_encode($activity_response));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }     

        $activity_data = isset(json_decode($activity_response['message'])[0]) ? json_decode($activity_response['message'])[0] : [];

        //Get activity's owner
        $user_id = isset($activity_data->uid) ? $activity_data->uid : "";
        //Generate the URL for get user information
        $url = $base_url .( $is_owner || $is_commissioner ? 'api/user': 'public/api/user').'?_formt=json&id=' . $user_id;
        
        $get_organization = BizWebformController::execute_external_api($url, [], 'GET', $request_options);
        if($get_organization['code'] == 400){
            \Drupal::logger('InHouseActivity')->error($url . ': '. json_encode($get_organization) .'; request_options: ' . json_encode($request_options));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }
        $organization = json_decode($get_organization["message"])[0];
        if(!empty($activity_data)){
            //Commissioners and owner can edit the activity
            if( $is_commissioner || ($is_owner && (($today >= $from && $today <= $to) || $activity_data->status !== 'Active'))){
                $base_href = '/in-house-account-home/in-house-activity-view/in-house-add-activity-edit?org=' . $id .'&';
                foreach($activity_data as $field => $value) { 
                        $value = str_replace('<br />', '%3Cbr%20/%3E', $value);
                        $base_href .=    $field . '=' . $value . '&'; 
                }
             $edit_activity =    $base_href;
            }
            //Only the Owner can certify
            if($is_owner){
                $content[] = array(
                    '#theme' => 'modal_confirmation',
                    '#title' => 'Accept Confirmation',
                    '#body_message' => 'Are you sure you want to certify this activity?',
                    '#label_yes' => 'Accept',
                    '#label_cancel'=> 'Cancel'
                );
                $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
                $certify = '<div class="accept-action col-md-4 col-md-6" id="btn-certify"><div class="btn btn-primary purple-button" data="'.$id.'&amp;add_a_lobbying_activity&amp;certify&amp;'. $organization->uid .'">I certify that this information is accurate</div></div>';
            }
            $content[] = [  '#theme' => 'in_house_account_info', 
                            '#organization'    => $organization,
                            "#link_edit_org" => $edit_organization, 
                            "#description" => t(' In-house lobbyist')
                        ];     
            $content[] = [  '#theme' => 'in_house_activity',
                            '#activity' => $activity_data,
                            "#link_edit_act" => $edit_activity ,
                            "#base_url" => $base_url 
                        ];

            //Print Comments
            if($is_commissioner || $is_owner){
                $content = array_merge($content, BizWebformController::get_comments_array($id));
            }
            
            if($is_commissioner || ( $is_owner && $activity_data->status !== 'Active')){
                // Add the comments to the page 
                $content[] = \Drupal::formBuilder()->getForm("Drupal\biz_activity_messages\Form\ActivityMessages");
                if(!$validate && $today >= $from && $today <= $to && $activity_data->status == 'Active') {
    			    $content[] = array('#type' => 'markup', '#markup' => $certify);
    		    }
            }
            //Only the commissioner can update the status to 'Non-compliant'
            if($is_commissioner){
                 $content[] = array(
                    '#theme' => 'modal_confirmation',
                    '#title' => 'Accept Confirmation',
                    '#body_message' => 'Are you sure you want to mark as "Non compliant"?',
                    '#label_yes' => 'Accept',
                    '#label_cancel'=> 'Cancel'
                );
                $update_status = '<div class="accept-action" id="btn-status"><div class="btn btn-primary purple-button" data="'.$id.'&amp;add_a_lobbying_activity&amp;frontend_status&amp;0">Mark as Non compliant</div></div>';
            }
            $content[] = array('#type' => 'markup', '#markup' => $update_status);
            return $content;
        }
    }
    /****
        * Disable caching for this block.
        */
    public function getCacheMaxAge() {
        return 0;
    }
}