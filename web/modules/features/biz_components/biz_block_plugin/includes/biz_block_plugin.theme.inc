<?php

/**
 * @file
 * Theme hooks, preprocessor, and suggestions.
 */

use Drupal\file\Entity\File;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Template\Attribute;

/******************************************************************************/
// Theme hooks.
/******************************************************************************/

/**
 * Implements hook_theme().
 */
 
function biz_block_plugin_theme() {
  $info = [
    'custom_table' => [
      'variables' => ['attributes' => [], 'colgroups' => [], 'header' => [], 'rows' => [],'footer' =>  '', 
      'empty'  => '','header_columns' => 0, 'bordered' => FALSE, 'condensed' => FALSE, 'hover' => FALSE, 'striped' => FALSE,
      'responsive' => TRUE, 'header_color' => '', 'caption' => '', 'sort_column' => '','sort_order' => '', 'base_url' => '']
    ],
    'consultant_account_info' => [
      'variables' => ['organization' => [], 'link_edit_org' => '', 'description' => '']
    ],
    'consultant_activity' => [
      'variables' => ['activity' => [], 'link_edit_act' => '' ]
    ],
    'in_house_account_info' => [
      'variables' => ['organization' => [], 'link_edit_org' => '', 'description' => '']
    ],
    'in_house_acount_organization_info' => [
      'variables' => ['organization' => [], 'link_edit_org' => '', 'description' => '']
    ],
    'in_house_activity' => [
      'variables' => ['activity' => [], 'link_edit_act' => '' ]
    ],
    'search_activities_general' => [
      'allowed_tags' => ['button','form', 'input', 'div', 'label'] 
    ],
    'search_block_home' => [
      'allowed_tags' => ['button','form', 'input', 'div', 'label'] 
    ],
    'activity_messages' => [
      'variables' => ['subject' => '', 'user_name' => '', 'message' => '', 'date' => ''  ]
    ],
    'modal_confirmation' => [
      'variables' => ['title' => '', 'label_yes' => '', 'label_cancel' => '', 'date' => ''  ]
    ],

  ];

  // Since any rendering of a webform is going to require 'webform.theme.inc'
  // we are going to just add it to every template.
  foreach ($info as &$template) {
    $template['file'] = 'includes/biz_block_plugin.theme.template.inc';
  }
  return $info;
}

