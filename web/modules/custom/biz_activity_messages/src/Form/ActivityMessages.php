<?php
  namespace Drupal\biz_activity_messages\Form;
  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\biz_webforms\BizWebformController;

  class ActivityMessages extends FormBase {

   /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'form_activity_messages';
  }  
   /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $form['comment_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reply message'),
      '#description' => $this->t(''),
      '#required' => TRUE,
    ];
    
    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#field_suffix' => '</div>',
      
    ]; 
    $form['actions']['submit']['#attributes']['class'][] = 'orange-button';
    
    if(in_array("role_administrator", $roles)){
      $form['button'][] = [
        '#type' => 'submit',
        '#value' => $this->t('Approve'),
        '#submit' => array('::updateStatusActivity'),
        '#attributes' => ['class' => ['orange-button']],
        '#field_prefix' => '<div class="buttons-container">',
      ];
      $form['button'][] = [
        '#type' => 'submit',
        '#value' => $this->t('Reject'),
        '#validate' => array('::validateForm'),
        '#submit' => array('::submitForm', '::updateStatusActivity'),
        '#attributes' => ['class' => ['orange-button']],
        
      ];
    }
    return $form;

  }
  function updateStatusActivity(&$form, FormStateInterface $form_state) {
    $event = $form_state->getTriggeringElement();
    $event = isset($event['#value']) ? $event['#value'] : "";
    $status = '';
    switch($event){
      case 'Approve':
        $status = 'active';
      break;
      case 'Reject':
         $status = 'not-compliance';
      break;
    }
    $submission_id = \Drupal::request()->query->get('id');
    $webform_id = \Drupal::request()->query->get('webform_id');
    $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
    $url = $base_url . 'webform_rest/' . $webform_id . '/submission/' . $submission_id;
    $data = ['status' => $status];
    BizWebformController::patch_endpoint($url, $data);
  }

   /**
   * Validate the title and the checkbox of the form
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * 
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $message = $form_state->getValue('comment_body');
    if (empty($message)){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('comment_body', $this->t('The message is required.'));
    }
  }
      
  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $comment_body = $form_state->getValue('comment_body');
    $base_url = \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
    //Get all query params
    $param = \Drupal::request()->query->all();
    //Get activity ID
    $id = isset($param['id']) ? $param['id'] : "0";
    $url = $base_url . \Drupal::config('biz_block_plugin.settings')->get('get_node_id') ."?_format=json&id=" . $id;
    $get_node_id = BizWebformController::get_endpoint($url, [], "GET", []);
    if($get_node_id['code'] !== 400){
        $get_node_id = json_decode($get_node_id["message"])[0];
        $node_id =  isset($get_node_id->nid) ? $get_node_id->nid : 0;
        $url = $base_url . "comment?_format=json";
        $current_user = \Drupal::currentUser();
        $data = [ 'entity_id' => [['target_id' => $node_id]], 
                  'entity_type' => [['value' => 'node']], 
                  'comment_type' => [['target_id' => 'comment']],  
                  'field_name' => [['value' => 'field_comments']],
                  'subject' => [['value' => '']],
                  'comment_body' => [['value' => $comment_body]],
                  'name' =>[['value' => $current_user->getEmail()]],
                ];
        \Drupal::logger('activity_messages')->notice(json_encode($data));
        $post_comment = BizWebformController::get_endpoint($url, $data, "POST", []);
    }
  }
}