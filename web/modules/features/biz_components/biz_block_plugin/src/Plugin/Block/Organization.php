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
  use Drupal\Core\Database\Database;
  use Drupal\Core\Database\ConnectionNotDefinedException;
  use Drupal\Core\Database\DatabaseExceptionWrapper;

/**
 * Provides a custom block.
 *
 * @Block(
 *   id = "organization_view_block",
 *   admin_label = @Translation("Organization view block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class Organization extends BlockBase implements BlockPluginInterface{

    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $content = [];
      $email = \Drupal::currentUser()->getEmail();
      $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $header_activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_activities');

      //Get params if exist and needs to be filtered
      $param = \Drupal::request()->query->all();
      $id_organization = trim(isset($param['id']) ? $param['id'] : '');
      //Generate the URL for get backend user information
      $url = $url_base . \Drupal::config('biz_block_plugin.settings')->get('get_user_by_id') . '?id='. $id_organization;
      //Get organization data
      $get_organization = BizWebformController::get_endpoint($url, [], "GET", []);
      if($get_organization['code'] !== 400){
        $organization = isset(json_decode($get_organization['message'])[0]) ? json_decode($get_organization['message'])[0] : [];
        $activity_email = $organization->mail;
      }
      $user_email = isset($organization->mail) ? $organization->mail : "";
      //Check if the user is the owner activity
      if(($email === $activity_email) && (!empty($email) && !empty($activity_email))){
        $edit_organization = 'user/' . \Drupal::currentUser()->id() . '/edit';
      }
      if(isset($organization->roles_target_id)){      
        if(strpos($organization->roles_target_id , 'in_house_lobbyist') !== FALSE){
          $content[] = array( '#theme' => 'in_house_account_info', '#organization'  => $organization,  "#link_edit_org" => $edit_organization, "#description" => t(' In-house lobbyist')); 
          $activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('search_in_house_activities');
          $lobbyist_endpoind = \Drupal::config('biz_block_plugin.settings')->get('in_house_lobbying_list');
          $header_lobbyist_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_in_house_lobbying');
          $header_lobbyist = GeneralFunctions::getHeadersTable($header_lobbyist_endpoind, FALSE);
          //Generate the URL for get backend user information
          $url = $url_base . $lobbyist_endpoind . '?_format=json&email='. $user_email;
          $lobbyist_rows = BizWebformController::get_endpoint($url, [], "GET", []);
          $lobbyist_rows = json_decode($lobbyist_rows['message']);
          $lobbyist_rows = GeneralFunctions::generateTableRows($header_lobbyist, $lobbyist_rows);
          //kint($edit_organization);
          if(empty($edit_organization) || $edit_organization === NULL){
            unset($header_lobbyist['header'][2]);
            unset($header_lobbyist['fields'][2]);
            unset($header_lobbyist['header'][3]);
            unset($header_lobbyist['fields'][3]);
            foreach ($lobbyist_rows as $key => $value) {
              unset($lobbyist_rows[$key]['data']->email);
              unset($lobbyist_rows[$key]['data']->telephone);
            }
          }
          $content[] = array(
            '#theme' => 'custom_table',
            '#header' => $header_lobbyist['header'],
            '#rows' => $lobbyist_rows,
            '#empty'=> 'This company doesn’t have any Lobbyist.',
            '#caption' => t('In-house Lobbyist'),
            '#attributes' => ['id' => 'org-lobbyist', 'class' => 'table-purple-header general-lobbyist-tables']
          );
        }elseif(strpos($organization->roles_target_id , 'consultant_lobbyist') !== FALSE){
          $content[] = array( '#theme' => 'consultant_account_info', 
                              '#organization'  => $organization,  
                              "#link_edit_org" => $edit_organization, 
                              '#description' => 'Organization');
          $activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('search_consultant_activities');  
        }
      }

      $header_response = GeneralFunctions::getHeadersTable($header_activities_endpoind, 'header-orange');
      //Generate the URL for get backend activities
      $url = $url_base . $activities_endpoind . "?_format=json&email=" . $user_email;
      $activities_response = BizWebformController::get_endpoint($url, [], "GET", []);
      $activity_rows = [];
      $rows_response = json_decode($activities_response['message']);
      $activity_rows = GeneralFunctions::generateTableRows($header_response, $rows_response);  
      $tableHeader = $header_response['header'];
      $content[] = array(
        '#theme' => 'custom_table',
        '#header' => $tableHeader,
        '#rows' => $activity_rows,
        '#empty'=> 'This company doesn’t have activities.',
        '#caption' => t('Activities'),
        '#attributes' => ['id' => 'org-activities', 'class' => 'table-purple-header general-lobbyist-tables']
      );
      return $content;
    }  
  }