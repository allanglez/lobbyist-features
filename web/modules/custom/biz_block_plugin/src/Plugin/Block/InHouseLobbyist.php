<?php
namespace Drupal\biz_block_plugin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\biz_webforms\BizWebformController;
use Drupal\biz_block_plugin\Controller\GeneralFunctions;

/**
  * Provides a custom block.
  *
  * @Block(
  *   id = "inhouselobbyist_custom_block",
  *   admin_label = @Translation("In-house Lobbyist (Organization) block"),
  *   category = @Translation("Bizont custom block")
  * )
  */
class InHouseLobbyist extends BlockBase implements BlockPluginInterface{

    /****
        * Distplay the in-house account info
        */
    public function build(){
        $edit_organization = '';
        $current_user = \Drupal::currentUser();
        $email = $current_user->getEmail();
        $roles = $current_user->getRoles();
        $base_url =    \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $base_href = '/in-house-in-organization-edit?';
        $header_lobbyists_endpoint = \Drupal::config('biz_block_plugin.settings')->get('header_in_house_lobbying');

        $header_response = GeneralFunctions::getHeadersTable($header_lobbyists_endpoint);
        $lobbyist_endpoint = \Drupal::config('biz_block_plugin.settings')->get('in_house_lobbying_list');
        $lobbyist_response = GeneralFunctions::getAllData($lobbyist_endpoint, TRUE);
        $url = $base_url . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . \Drupal::currentUser()->getEmail();
        $request_options = BizWebformController::get_request_options(TRUE);
        $get_organization = BizWebformController::execute_external_api($url, [], 'GET', $request_options);
        if($get_organization['code'] == 400){
            \Drupal::logger('ConsultantActivity')->error(json_encode($get_organization));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }
        $organization = json_decode($get_organization["message"])[0];
        $lobbyist_email = isset($organization->mail) ? $organization->mail : "";

        if($header_response && $lobbyist_response) {
            $rows_response = json_decode($lobbyist_response['message']);
            foreach ($rows_response as $key => $data) {
    	        foreach($data as $field => $value) { 
    		        if ($field != 'actions') {
    	            		$base_href .=  $field . '=' . $value . '&'; 
    	            }
    	        }
    	        $data->actions = str_replace('[[custom_href]]', $base_href, $data->actions);
            }
            $lobbyist_rows = GeneralFunctions::generateTableRows($header_response, $rows_response); 
        }
        //Check if the user is In-House Lobbyist
        if($email === $lobbyist_email && $organization->roles_target_id == 'In-house lobbyist'){
            $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
        }  
        $content[] = array( '#theme' => 'in_house_account_organization_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t(' In-house lobbyist'));

        
        $content[] = array('#theme' => 'custom_table',
                           '#header' => $header_response['header'],
                           '#rows' => $lobbyist_rows,
                           '#empty'=> "This company doesn't have any Lobbyist.",
                           '#caption' => t('In-house Lobbyist'),
                           '#attributes' => ['id' => 'internal-lobbyist', 'class' => 'table-orange-header general-lobbyist-tables'],
                           '#base_url' => $base_url
                        );
        
        return $content;
    }

    /****
        * Disable caching for this block.
        */
    public function getCacheMaxAge() {
        return 0;
    }
}