<?php
namespace Drupal\biz_lobbyist_registration\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Configure example settings for this site.
*/
class RegistrationSettingsForm extends ConfigFormBase {

  /** 
  * Config settings.
  *
  * @var string
  */
  const SETTINGS = 'biz_lobbyist_registration.settings';

  /** 
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'biz_lobbyist_registration_admin_settings';
  }

  /** 
  * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /** 
  * Build a form with textfields for this settings var configurations
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    
    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BaseURL'),
      '#default_value' => $config->get('base_url'),
    ];
    
    $form['validate_email_url_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL API for validate email'),
      '#default_value' => $config->get('validate_email_url_api'),
    ];  
    $form['registration_url_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL API for create account'),
      '#default_value' => $config->get('registration_url_api'),
    ];     
    $form['json_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Json Path for user'),
      '#default_value' => $config->get('json_path'),
    ];
    $form['error_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom error message'),
      '#default_value' => $config->get('error_message'),
    ];

    
    return parent::buildForm($form, $form_state);
  }

  /** 
  * function for setting var configuration submission
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('registration_url_api', $form_state->getValue('registration_url_api'))
      ->set('validate_email_url_api', $form_state->getValue('validate_email_url_api'))
      ->set('json_path', $form_state->getValue('json_path'))
      ->set('base_url', $form_state->getValue('base_url'))
      ->set('error_message', $form_state->getValue('error_message'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}