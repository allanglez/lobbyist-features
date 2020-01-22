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
    public function build() {
      $content = [];
      $email = \Drupal::currentUser()->getEmail();
      $lobbyist_endpoind = \Drupal::config('biz_block_plugin.settings')->get('in_house_lobbying_list');
      $header_lobbyist_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_in_house_lobbying');
      $header_response = GeneralFunctions::getHeadersTable($header_lobbyist_endpoind);
      $activities_response = GeneralFunctions::getAllData($lobbyist_endpoind); 
      $url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url') . \Drupal::config('biz_lobbyist_registration.settings')->get('json_path') . \Drupal::currentUser()->getEmail();
      $get_organization = BizWebformController::get_endpoint($url, [], "GET", []);
      if($get_organization['code'] !== 400){
        $organization = json_decode($get_organization["message"])[0];
        if($organization->roles_target_id == 'Consultant lobbyist')
          $edit_organization = 'user/' . \Drupal::currentUser()->id() . '/edit'; 
                 
          $content[] = array( '#theme' => 'consultant_account_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t('Consultant information'));   
      }
      return $content;
    }
    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }

  
  }
  