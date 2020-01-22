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
 *   id = "search_organization_block",
 *   admin_label = @Translation("Search Organization block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class SearchOrganization extends BlockBase implements BlockPluginInterface{

    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $content = [];
      //Get params if exist and needs to be filtered
      $param = \Drupal::request()->query->all();
      $name_organization = trim(isset($param['organization-name']) ? $param['organization-name'] : '');
      $department = trim(isset($param['department']) ? $param['department'] : '') ;
      $query_param = "";
      $url_base = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
      $header_activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_all_organizations');
      $activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('all_organizations');
 
      $header_response = GeneralFunctions::getHeadersTable($header_activities_endpoind);
      
      $query_param = !empty($name_organization) ? '&combine='.$name_organization: '' ;
      $query_param .= !empty($department) ?  '':'';
      $url = $url_base . $activities_endpoind . "?_format=json" . $query_param; 
      $activities_response = BizWebformController::get_endpoint($url, [], 'GET', []); 
      $activity_rows = [];
      $activity_row = [];
      if($header_response && $activities_response){
        $rows_response = json_decode($activities_response['message']);
        $temp = array_unique(array_column($rows_response, 'organization_name')); 
        $rows_response = array_intersect_key($rows_response, $temp);
        $activity_rows = GeneralFunctions::generateTableRows($header_response, $rows_response);
        $content[] = array(
          '#theme' => 'custom_table',
          '#header' => $header_response['header'],
          '#rows' => $activity_rows,
          '#empty'=> 'Not exist any organization.',
          '#attributes' => ['id' => 'organizations', 'class' => 'table-orange-header general-lobbyist-tables']
        );
      }
      return $content;
    }  
  }