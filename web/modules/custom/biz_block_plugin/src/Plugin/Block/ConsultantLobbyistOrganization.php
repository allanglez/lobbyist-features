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
  *   id = "info_consultant_org_custom_block",
  *   admin_label = @Translation("Consultant Lobbyist (Organization) block"),
  *   category = @Translation("Bizont custom block")
  * )
  */
class ConsultantLobbyistOrganization extends BlockBase implements BlockPluginInterface{

    /****
        * Display the consultant account info
        */
    public function build() {
        $content = [];
        $email = \Drupal::currentUser()->getEmail();      
        $url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url') . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . \Drupal::currentUser()->getEmail();
        $request_options = BizWebformController::get_request_options(TRUE);
        $get_organization = BizWebformController::execute_external_api($url, [], "GET", $request_options);

        if($get_organization['code'] == 400){
            \Drupal::logger('ConsultantLobbyistOrganization')->error($url . ': '. json_encode($get_organization) .'; request_options: ' . json_encode($request_options));
            drupal_set_message(t('The website encountered an unexpected error. Please try again later.'), 'error');
            return FALSE;
        }

        $organization = json_decode($get_organization["message"])[0];
        if($organization->roles_target_id == 'Consultant lobbyist'){
            $edit_organization = 'user/' . \Drupal::currentUser()->id() . '/edit';
        }
        $content[] = array( '#theme' => 'consultant_account_info', 
                            '#organization'  => $organization, 
                            '#link_edit_org' => $edit_organization,
                            '#description' => t('Consultant information'));   

        return $content;
    }

    /****
        * Disable caching for this block.
        */
    public function getCacheMaxAge() {
        return 0;
    }
}