<?php
namespace Drupal\biz_block_plugin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;

/**
  * Provides a custom block.
  *
  * @Block(
  *   id = "header_search_block",
  *   admin_label = @Translation("Header Search activity block"),
  *   category = @Translation("Bizont custom block")
  * )
  */
class HeaderSearchBlock extends BlockBase implements BlockPluginInterface{

    /****
        * Block for search activities
        */
    public function build() {
      $content[] = array('#theme' => 'search_activities_general');
      return $content;
    }
    
    /****
        * Disable caching for this block.
        */
    public function getCacheMaxAge() {
        return 0;
    }  
}
  