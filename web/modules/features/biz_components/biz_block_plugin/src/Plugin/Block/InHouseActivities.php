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
 *   id = "inhouse_activities_block",
 *   admin_label = @Translation("In-house Activities block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class InHouseActivities extends BlockBase implements BlockPluginInterface{
    
    public function getCacheMaxAge() {
      return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $email = \Drupal::currentUser()->getEmail();
      $header_activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('header_activities');
      $activities_endpoind = \Drupal::config('biz_block_plugin.settings')->get('in_house_activities');
      $header_response = GeneralFunctions::getHeadersTable($header_activities_endpoind);
      $activities_response = GeneralFunctions::getAllData($activities_endpoind);  
      $activity_rows = [];
      $activity_row = [];
    
      if($header_response && $activities_response){
        $rows_response = json_decode($activities_response['message']);
        $activity_rows = GeneralFunctions::generateTableRows($header_response, $rows_response);
        if(!empty($activity_rows)){
          $content[] =array(
            '#theme' => 'custom_table',
            '#header' => $header_response['header'],
            '#rows' => $activity_rows,
            '#empty'=> 'You don’t have activities.',
            '#caption' => t('Lobbying activities you are associated with'),
            '#attributes' => ['id' => 'in-house-activities', 'class' => 'table-orange-header general-lobbyist-tables'], 
            '#sort_column' => '1',
            '#sort_order' => 'desc'
          ); 
        }
      }
      $content[] = array(
        '#type' => 'markup',
        '#markup' => '<div class="add-lobb-act-content"><a type="button" href="/in-house-account-home/in-house-add-activity" class="btn btn-primary purple-button">'.t('Add a lobbying activity').'</a></div>',
      );
      return $content;
      
    }
  }
  