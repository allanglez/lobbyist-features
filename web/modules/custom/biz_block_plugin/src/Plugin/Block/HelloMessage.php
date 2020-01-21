<?php
  namespace Drupal\biz_block_plugin\Plugin\Block;
  
  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Block\BlockPluginInterface;
  use Drupal\Core\Form\FormBuilderInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Access\AccessResult;
  use Drupal\Core\Cache\Cache;
  use Drupal\biz_webforms\BizWebformController;
/**
 * Provides a custom block.
 *
 * @Block(
 *   id = "hello_msg_block",
 *   admin_label = @Translation("Hello Message block"),
 *   category = @Translation("Bizont custom block")
 * )
 */
  class HelloMessage extends BlockBase implements BlockPluginInterface{
    public function getCacheMaxAge() {
        return 0;
    }
    /**
     * {@inheritdoc}
    */
    public function build() {
      $user = \Drupal::currentUser();
      $user_entity = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($user->id());
      $roles = $user->getRoles();
      $name = '';
      $first_name = "";
      $last_name = "";
      if(in_array('in_house_lobbyist', $roles)){
        $first_name = $user_entity->get('field_first_name')->getValue();
        $first_name = isset($first_name[0]) ? $first_name[0]['value'] : "";
        $last_name = $user_entity->get('field_last_name')->getValue();
        $last_name = isset($last_name[0]) ? $last_name[0]['value'] : "";
        $name = $first_name . ' ' . $last_name;

        $url = '/in-house-account-home';
      }elseif(in_array('consultant_lobbyist', $roles)){
        $first_name = $user_entity->get('field_first_name_consultant_')->getValue();
        $first_name = isset($first_name[0]) ?  $first_name[0]['value'] : "";
        $last_name = $user_entity->get('field_last_name_consultant_')->getValue();
        $last_name = isset($last_name[0]) ?  $last_name[0]['value'] : "";
        $name = $first_name . ' ' . $last_name;

        $url = '/consultant-account-home';
      }elseif(in_array('role_administrator', $roles)){
        $name = $user->getUsername();
        $url = '/commissioner';
      }
      if(!empty($name)){
        $tag = '<a href='.$url.'>'.t('Hello').', '.$name.'</a>';
        $content[] = array( '#type' => 'markup', '#markup'  => $tag  );
        return $content;
      }
    }
  }
  