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
 * Provides a resource to activate user with token.
 *
 * @RestResource(
 *   id = "register_verify_email_token",
 *   label = @Translation("Activate user Via Temp token"),
 *   uri_paths = {
 *     "canonical" = "/rest/verify-account",
 *     "https://www.drupal.org/link-relations/create" = "/rest/verify-account"
 *   }
 * )
 */
class ActivateUserWithTempTokenRestResource extends ResourceBase {

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
   * Constructs a new ActivateUserWithTempTokenRestResource Resource object.
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
   * Responds Post {"name":"username", "temp_token":"TEMPPASS"}
   *
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $responce = ['message' => 'Oops! Something went wrong.'];
    $code = 400;
    if (!empty($data['name']) && !empty($data['temp_token'])) { //} && !empty($data['new_pass'])) {
      $name = $data['name'];
      $temp_token = $data['temp_token'];
      //$new_pass = $data['new_pass'];

      // Try to load by email.
      $users = $this->userStorage->loadByProperties(['name' => $name]);
      if (!empty($users)) {
        $account = reset($users);
        if ($account && $account->id()) {

          // CUSTOM
          // Check the temp token
          $uid = $account->id();
          $service = \Drupal::service('tempstore.shared');
          $collection = 'rest_register_verify_email';
          $tempstore = $service->get($collection, $uid);
          $temp_token_from_storage = $tempstore->getIfOwner('temp_token');
          if (!empty($temp_token_from_storage)) {
            if ($temp_token_from_storage === $temp_token) {
              // Cool.... lets activate our user.
              $account->activate();
              $account->save();
              $code = 200;
              $responce = ['message' => 'Your Account has been Activated, please log in.'];
              // delete temp token.
              $tempstore->deleteIfOwner('temp_token');
            }
            else {
              $responce = ['message' => 'Invalid temp token provided.'];
            }
          }
          else {
            $responce = ['message' => 'No valid temp token request.'];
          }
          
        }
        else {
          $responce = [
            'message' => 'Sorry, there was a problem - please try again.',
            'error' => 'This User has no ID'
          ];
        }
      }
      else {
        $responce = [
          'message' => 'Sorry, there was a problem - please try again.',
          'error' => 'This User was not found or invalid'
        ];
      }
    }
    else {
      $responce = [
        'message' => 'Sorry, there was a problem - please try again.',
        'error' => 'name and temp_token fields are required'
      ];
    }

    return new ResourceResponse($responce, $code);
  }

}
