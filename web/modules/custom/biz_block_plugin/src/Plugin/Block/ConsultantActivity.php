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
    
    /****
        * Display a specific consultant activity
        */
    public function build() {
        $base_url =    \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $validate = \Drupal::config('biz_business_rules.settings')->get('wave_for_not_validate');
        $request_options_public = BizWebformController::get_request_options(FALSE);
        $current_user = \Drupal::currentUser();
        $email = !empty($current_user->getEmail()) ? $current_user->getEmail(): 'anonymous';
        $roles = $current_user->getRoles();
        $today = date("Y-m-d");
        $is_owner = FALSE;
        $is_commissioner = in_array("role_administrator", $roles);
        //Get all query params
        $param = \Drupal::request()->query->all();
        //Get activity ID
        $id = isset($param['id']) ? $param['id'] : "0";
        //Get activity data
        $edit_activity = "";
        $edit_organization = "";
        $organization = [];
        $activity_data = [];
        $content = [];
        $certify = "";
        $url_get_endpoint = $base_url. 'api/activity/'.$id.'/add_a_lobbying_activity_consulta/' . $email . '?_format=json' ;
        $activity_endpoint = BizWebformController::execute_external_api($url_get_endpoint, [], "GET", $request_options_public);

        if($activity_endpoint['code'] == 400){
            \Drupal::logger('ConsultantActivity')->error(json_encode($activity_endpoint['message']));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }

        $activity_endpoint['message'] = json_decode($activity_endpoint['message']);
        $is_owner = $activity_endpoint['message']->owner; 
        $auth = $is_commissioner || $is_owner ? TRUE : FALSE;
        $request_options = BizWebformController::get_request_options($auth);
        $activity_endpoint = $activity_endpoint['message']->endpoint;
        $activity_response = GeneralFunctions::getSubmission($activity_endpoint, $id, $auth);

        if($activity_response['code'] == 400){
            \Drupal::logger('ConsultantActivity')->error(json_encode($activity_response['message']));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }
        $activity_data = isset(json_decode($activity_response['message'])[0]) ? json_decode($activity_response['message'])[0] : [];
        //Get activity's owner
        $user_id = isset($activity_data->uid) ? $activity_data->uid : "";
        $last_updated = $activity_data->last_updated;
        //Generate the URL for get user information
        $url = $base_url .( $is_owner || $is_commissioner ? 'api/user': 'public/api/user').'?_formt=json&id=' . $user_id;
        //Get organization data
        $get_organization = BizWebformController::execute_external_api($url, [], 'GET', $request_options);
        if($get_organization['code'] == 400){
            \Drupal::logger('ConsultantActivity')->error(json_encode($get_organization));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }
        $organization = isset(json_decode($get_organization['message'])[0]) ? json_decode($get_organization['message'])[0] : [];
        //Check if the user is the owner activity
        if($is_owner){
            $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
            $certify = '<div class="accept-action" id="btn-certify"><div id = "btn-certify" class="btn btn-primary purple-button" data="'.$id.'&amp;add_a_lobbying_activity_consulta&amp;certify&amp;'. $organization->uid .'">I certify that this information is accurate</div></div>';
            $url_get_last_updated = $base_url . 'biz_custom_rest_api/custom_resource/add_a_lobbying_activity_consulta/' . $id . '/'.$activity_data->uid .'?_format=json';

            $get_organization = BizWebformController::execute_external_api($url_get_last_updated, [], 'GET', $request_options_public);

            if($get_last_updated['code'] !== 400){
                $get_last_updated = json_decode($get_last_updated['message']);
                $last_updated = $get_last_updated->last_updated;
            }
        }

        // creates DateTime objects 
        $dt_today = date_create(); 
        $dt_start_date = date_create($activity_data->start_date); 
        $dt_end_date = $activity_data->end_date; 
        $dt_end_date_30 = date("Y-m-d", strtotime($activity_data->end_date . ' +30 day'));

        // calculates the difference between start date and today
        $interval = date_diff($dt_today, $dt_start_date); 
        $diff_months = intval($interval->format('%m'));
        $diff_years= intval($interval->format('%y')); 
        $months = (floor($diff_months / 6) == 0 ? 1: floor($diff_months / 6))    * 6;
        $years = floor($diff_years) == 0 ? 0: floor($diff_years * 12);
        $total_months = $months + $years;        
        $after_6_month = date("Y-m-d", strtotime( $activity_data->start_date . ' +'. $total_months .' month'));
        $from =    date("Y-m-d", strtotime($after_6_month . ' +1 day'));
        $to = date("Y-m-d", strtotime( $after_6_month . ' +30 day'));

        if(!empty($activity_data) ){
            //Validate if the user should edit the activity
            if($is_commissioner ||  ($is_owner && 
                (($today >= $from && $today <= $to    &&    $last_updated < $from &&    $last_updated <= $to) || 
                 ($activity_data->status !== 'Active') || ( $today >= $dt_end_date && $today <= $dt_end_date_30)))){
                $base_href = '/consultant-account-home/consultant-activity-view/consultant-add-activity-edit?org=' . $id .'&';
                foreach($activity_data as $field => $value) {
                        $value = str_replace('<br />', '%3Cbr%20/%3E', $value); 
                        $base_href .=    $field . '=' . $value . '&'; 
                }
                $edit_activity =    $base_href;
            }
        }
        $content[] =['#theme' => 'consultant_account_info', 
                      '#organization'    => $organization,
                      '#link_edit_org' => $edit_organization,
                      '#description' => t('Consultant lobbyist')
                    ];     
        $content[] =['#theme' => 'consultant_activity',
                     '#activity' => $activity_data,
                     '#link_edit_act' => $edit_activity,
                     '#base_url' => $base_url 
                    ]; 

        //Print Comments
        if($is_commissioner || $is_owner){
            $content = array_merge($content, BizWebformController::get_comments_array($id));
        }
        //Print Form
        if($is_commissioner || ($is_owner && $activity_data->status != 'Active') ){
            if($comments_empty){
                $content[] = array('#type' => 'markup', '#markup' => $title);
            }
            $content[] = \Drupal::formBuilder()->getForm("Drupal\biz_activity_messages\Form\ActivityMessages");
        }
        //Print Certify button    
        if(!$validate &&    $is_owner && (($today >= $from && $today <= $to) || ($today >= $dt_end_date && $today <= $dt_end_date_30)) &&    ($last_updated < $from &&    $last_updated <= $to)){
                $content[] = array(
                    '#theme' => 'modal_confirmation',
                    '#title' => 'Accept Confirmation',
                    '#body_message' => 'Are you sure you want to certify this activity?',
                    '#label_yes' => 'Accept',
                    '#label_cancel'=> 'Cancel'
                );
			    $content[] = array('#type' => 'markup', '#markup' => $certify);
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

        return $content;
    }
    
    /****
        * Disable caching for this block.
        */
    public function getCacheMaxAge() {
        return 0;
    }
}