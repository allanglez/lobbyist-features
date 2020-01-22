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
 *   id = "commissioner_act_block",
 *   admin_label = @Translation("Commissioner Activities block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class CommissionerActivities extends BlockBase implements BlockPluginInterface{

    /**
     * {@inheritdoc}
    */
    public function build() {
      $current_user = \Drupal::currentUser();
      $email = $current_user->getEmail();
      $roles = $current_user->getRoles();   
      $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
   
      if(in_array("role_administrator", $roles)){
        $content[] = array(
            '#theme' => 'modal_confirmation',
            '#title' => 'Accept Confirmation',
            '#label_yes' => 'Accept',
            '#label_cancel'=> 'Cancel'
          );

        $header_activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_commissioner_activities');
        $activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('commissioner_activities');

        $header_response = GeneralFunctions::getHeadersTable($header_activities_endpoind, 'header-orange');
        $activities_response = GeneralFunctions::getAllData($activities_endpoind);  
        $activity_rows = [];
        $activity_row = [];
        if($header_response && $activities_response){
          $rows_response = json_decode($activities_response['message']);
          $activity_rows = GeneralFunctions::generateTableRows($header_response, $rows_response); 
        }
        if(!empty($activity_rows)){
          $content[] = array(
            '#theme' => 'custom_table',
            '#header' => $header_response['header'],
            '#rows' => $activity_rows,
            '#empty'=> "You don't have activities.",
            '#caption' => t('Activities'),
            '#attributes' => ['id' => 'commissioner-activities', 'class' => 'table-orange-header general-lobbyist-tables'], 
            '#sort_column' => '3',
            '#sort_order' => 'desc',
            '#base_url' => $base_url
          );
        }
      }
      return $content;
    }
    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
  }
  