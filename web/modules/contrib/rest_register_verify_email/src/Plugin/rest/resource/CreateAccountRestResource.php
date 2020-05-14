<?php

namespace Drupal\rest_register_verify_email\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\user\UserStorageInterface;

/**
 * Provides a resource to Email verification token to user.
 *
 * @RestResource(
 *   id = "register_verify_email_resource",
 *   label = @Translation("Create account"),
 *   uri_paths = {
 *     "canonical" = "/rest/create-account",
 *     "https://www.drupal.org/link-relations/create" = "/rest/create-account"
 *   }
 * )
 */
class CreateAccountRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new CreateAccountRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    UserStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->userStorage = $user_storage;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_register_verify_email'),
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('user')
    );
  }

  /**
   * Responds to POST requests with mail . and lang
   *
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $responce = ['message' => 'Please Post mail key.'];
    $code = 400;
    if (!empty($data['mail']) && !empty($data['pass'])) {
      $email = $data['mail'];
      $pass = $data['pass'];
      $lang = NULL;
      if (!empty($data['lang'])) {
        $lang = $data['lang'];
      }

      // similar to D8 Register endpoint, allow adding user fields during this process
      // get the keys of the data sent which start with 'field_'
      $other_fields = array_values(array_filter(array_keys($data), function($key) {
        return is_string ($key) && strpos($key, 'field_') === 0; // key could be an int...
      }));

  
      //CUSTOM: register 

      /* 
        if the email is a duplicate, Drupal will respond with a 500 error:
        Drupal\Core\Entity\EntityStorageException: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry

        To avoid this, try catching that error
      */
      try {
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword($pass);
        $user->enforceIsNew();
        $user->setEmail($email);
        $user->setUsername($email);
        // Optional settings 
        $user->set("init", $email);
        if (isset($lang)) {
          $user->set("langcode", $lang);
          $user->set("preferred_langcode", $lang);
          // $user->set("preferred_admin_langcode", $lang);
        }
        //$user->set("setting_name", 'setting_value');

        // add the custom fields
        foreach ($other_fields as $k) {
          // check that user account fields exist
          if ($user->hasField($k)) {
            // $this->logger->notice('Setting custom field %field to %val.', ['%field' => $k, '%val' => $data[$k]]);
            $user->set($k, $data[$k]);
          }
        }
    
        // $user->activate();
        $user->block();
        // verify this is going to work (https://www.drupal.org/docs/8/api/entity-validation-api/entity-validation-api-overview)
        $violations = $user->validate();
        if ($violations->count() > 0) {
          // Validation failed.
          $code = 400;
          $responce = [
            'message' => strip_tags($violations[0]->getMessage()),//'Uh oh, we caught an error... please try again.', 
            'error' => '$user->validate() violation'
          ];
        } else {
          // validated, it should work
          $user->save();


          // Mail a temp token.
          $mail = _rest_register_verify_email_user_mail_notify('email_verify_register_rest', $user, $lang);
          if (!empty($mail)) {
            $this->logger->notice('Account Verification Token instructions mailed to %email.', ['%email' => $email]);
            $responce = ['message' => 'Further instructions have been sent to your email address.'];
            $code = 200;
          }
          else {
            $responce = ['message' => 'Sorry system can\'t send email at the moment, but your account was created.'];
            $code = '400';
          }
        }
      
      }
      catch (Exception $e) {
        // Something went wrong somewhere
        \Drupal::logger('rest_register_verify_email')->error($e->getMessage());

        $code = 400;
        $responce = [
          'message' => 'Uh oh, we caught an error... please try again.', 
          'error' => $e->getMessage()
        ];
      }
    


      // // OLD for REF
      // // Try to load by email.
      // $users = $this->userStorage->loadByProperties(['mail' => $email]);
      // if (!empty($users)) {
      //   $account = reset($users);
      //   if ($account && $account->id()) {
         
      //     // Blocked accounts cannot request a new password.
      //     if (!$account->isActive()) {
      //       $responce = t('This account is blocked or has not been activated yet.');
      //     }
      //     else {
      //       // Mail a temp password.
      //       $mail = _rest_register_verify_email_user_mail_notify('email_verify_register_rest', $account, $lang);
      //       if (!empty($mail)) {
      //         $this->logger->notice('Password temp password instructions mailed to %email.', ['%email' => $account->getEmail()]);
      //         $responce = ['message' => 'Further instructions have been sent to your email address.'];
      //         $code = 200;
      //       }
      //       else {
      //         $responce = ['message' => 'Sorry system can\'t send email at the moment'];
      //         $code = '400';
      //       }
      //     }
      //   }
      // }
      // else {
      //   $responce = ['message' => 'This User was not found or invalid'];
      // }
    }
    else {
      $responce = ['message' => 'Name and Password fields are required'];
    }

    return new ResourceResponse($responce, $code);
  }

}
