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
 *   id = "search_activity_home_block",
 *   admin_label = @Translation("Search activity home block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class SearchBlock extends BlockBase implements BlockPluginInterface{

    public function getCacheMaxAge() {
      // If you want to disable caching for this block.
      return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $content[] = array('#theme' => 'search_block_home');
      return $content;
    }  
  }
  