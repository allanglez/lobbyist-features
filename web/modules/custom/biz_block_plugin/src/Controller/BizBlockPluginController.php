<?php
  
/**
 * @file
 * Contains \Drupal\biz_block_plugin\Controller\BizBlockPluginController.
 */
 
namespace Drupal\biz_block_plugin\Controller;
 
use Drupal\Core\Controller\ControllerBase;
 
class BizBlockPluginController extends ControllerBase {
  public function tablesContent() {
    return [
      '#theme' => 'custom_table',
      '#attributes' => [], '#colgroups' => [], '#header' => [], '#rows' => [],'#footer' =>  '', 
      '#empty'  => '','#header_columns' => 0, '#bordered' => FALSE, '#condensed' => FALSE, '#hover' => FALSE, '#striped' => FALSE,
      '#responsive' => TRUE, '#header_color' => '', '#caption' => '' , '#sort_column' => '','#sort_order' => '', '#base_url' => ''
    ];
  }
  
  public function consultantActivityContent() {
    return [
      '#theme' => 'consultant_activity',
      '#activity' => [],
      '#link_edit_act' => '' 
    ];
  }
  public function consultantAccountInfo() {
    return [
      '#theme' => 'consultant_account_info',
      '#organization' => [],
      '#link_edit_org' => '',
      '#description'  => ''
    ];
  }
  public function inHouseAcountOrganizationInfo() {
    return [
      '#theme' => 'in_house_acount_organization_info',
      '#organization' => [],
      '#link_edit_org' => '',
      '#description'  => ''
    ];
  }
  public function searchActivitiesGeneral() {
    return [
      '#theme' => 'search_activities_general',
      '#allowed_tags' => ['button','form', 'input', 'div', 'label'] 
    ];
  }
  public function searchBlockHome() {
    return [
      '#theme' => 'search_block_home',
      '#allowed_tags' => ['button','form', 'input', 'div', 'label'] 
    ];
  }
  public function activityMessages() {
    return [
      '#theme' => 'activity_messages',
      '#subject' => '',
      '#user_name'  => '',
      '#message'  => '',
      '#date'  => ''
    ];
  } 
  public function modalConfirmation() {
    return [
      '#theme' => 'modal_confirmation',
      '#title' => '',
      '#label_yes'  => '',
      '#label_cancel'  => '',
    ];
  }
}
