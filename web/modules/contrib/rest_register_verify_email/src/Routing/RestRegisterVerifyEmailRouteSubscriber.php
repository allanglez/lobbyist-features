<?php

namespace Drupal\rest_register_verify_email\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RestRegisterVerifyEmailRouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RestRegisterVerifyEmailRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('rest.register_verify_email_resource.POST')) {
      $requirements = $route->getRequirements();
      if (!empty($requirements['_csrf_request_header_token'])) {
        unset($requirements['_csrf_request_header_token']);
        unset($requirements['_permission']);
        $options = $route->getOptions();
        unset($options['_auth']);
        $route->setOptions($options);
        $route->setRequirements($requirements);
      }
    }

    if ($route = $collection->get('rest.register_verify_email_token.POST')) {
      $requirements = $route->getRequirements();
      if (!empty($requirements['_csrf_request_header_token'])) {
        unset($requirements['_csrf_request_header_token']);
        unset($requirements['_permission']);
        $options = $route->getOptions();
        unset($options['_auth']);
        $route->setOptions($options);
        $route->setRequirements($requirements);
      }
    }

    //TODO: DO WE NEED THIS?
    // Need to alter user auth a bit.
    // if ($route = $collection->get('user.login.http')) {
    //   $defaults = $route->getDefaults();
    //   $defaults['_controller'] = '\Drupal\rest_register_verify_email\Controller\UserAuthenticationTempTokenController::login';
    //   $route->setDefaults($defaults);
    // }
  }
}
