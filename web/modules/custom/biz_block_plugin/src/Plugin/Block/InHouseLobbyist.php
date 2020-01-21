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
 *   id = "inhouselobbyist_custom_block",
 *   admin_label = @Translation("In-house Lobbyist (Organization) block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class InHouseLobbyist extends BlockBase implements BlockPluginInterface{
    /**
     * {@inheritdoc}
     */

     /*
    public function access(AccountInterface $account, $return_as_object = FALSE) {
      return \Drupal\Core\Access\AccessResult::allowedIf($account->isAuthenticated());
    }

    /**
     * {@inheritdoc}
    */
    public function build(){
      $email = \Drupal::currentUser()->getEmail();
      $lobbyist_endpoint = \Drupal::config('biz_block_plugin.settings')->get('in_house_lobbying_list');
      $header_lobbyist_endpoint = \Drupal::config('biz_block_plugin.settings')->get('header_in_house_lobbying');
      $header_response = GeneralFunctions::getHeadersTable($header_lobbyist_endpoint);
      $lobbyist_response = GeneralFunctions::getAllData($lobbyist_endpoint);  
      $base_href = '/in-house-in-organization-edit?';
      $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $url =  $base_url . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . \Drupal::currentUser()->getEmail();
      $get_organization = BizWebformController::get_endpoint($url, [], "GET", []);
      if($get_organization['code'] !== 400){
        $organization = json_decode($get_organization["message"])[0];
        $lobbyist_email = isset($organization->mail) ? $organization->mail : "";
        
      }
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
        $activity_rows = GeneralFunctions::generateTableRows($header_response, $rows_response); 
      }
      //Check if the user is In-House Lobbyist
      if($email === $lobbyist_email && $organization->roles_target_id == 'In-house lobbyist'){
        $edit_organization = '/user/' . \Drupal::currentUser()->id() . '/edit';
      }  
      $content[] = array( '#theme' => 'in_house_acount_organization_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t(' Consultant lobbyist'));
      if(!empty($activity_rows)) { 
        $content[] = array(
          '#theme' => 'custom_table',
          '#header' => $header_response['header'],
          '#rows' => $activity_rows,
          '#empty'=> "This company doesn't have any Lobbyist.",
          '#caption' => t('In-house Lobbyist'),
          '#attributes' => ['id' => 'org-lobbyist', 'class' => 'table-orange-header general-lobbyist-tables'],
          '#base_url' => $base_url
        );
      }
      
      $content[] = array(
        '#type' => 'markup',
        '#markup' => '<div class="add-lobb-act-content"><a type="button" href="/in-house-account-home/add_new_house_lobbyist" class="btn btn-primary purple-button">' . t(\Drupal::config('biz_lobbyist_registration.settings')->get('add_an_in_house_lobbyust_button_text')).'</a></div>',
      );      
      return $content;
    }

    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
  }