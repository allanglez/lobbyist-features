<?php
  namespace Drupal\biz_webforms\EventSubscriber;

  use Symfony\Component\HttpFoundation\RedirectResponse;
  use Symfony\Component\HttpKernel\KernelEvents;
  use Symfony\Component\HttpKernel\Event\GetResponseEvent;
  use Symfony\Component\EventDispatcher\EventSubscriberInterface;
  use Drupal\biz_block_plugin\Controller\GeneralFunctions;

  class BizWebformsSubscriber implements EventSubscriberInterface {

      public function checkForRedirection(GetResponseEvent $event) {
        $current_uri = \Drupal::request()->getRequestUri(); 
        $current_user = \Drupal::currentUser();
        $roles = $current_user->getRoles();
        $is_commissioner =  in_array("role_administrator", $roles) ? TRUE : FALSE;
        switch (TRUE) {
            case strpos($current_uri, '/in-house-account-home/in-house-add-activity-edit') !== FALSE:
            case strpos($current_uri, '/in-house-account-home/in-house-activity-view') !== FALSE :            
                self::validateOwnerInHouseActivity($id); 
            break;
            case strpos($current_uri, '/consultant-account-home/consultant-add-activity-edit') !== FALSE :
            case strpos($current_uri, '/consultant-account-home/consultant-activity-view') !== FALSE :
                self::validateOwnerConsultantActivity();
              break;
        }
      }

      /**
       * {@inheritdoc}
       */
      static function getSubscribedEvents() {
          $events[KernelEvents::REQUEST][] = array('checkForRedirection');
          return $events;

      }
      public function validateOwnerConsultantActivity() {
        $current_user = \Drupal::currentUser();
        $email = $current_user->getEmail();
        $base_url =  \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $activity_endpoint = \Drupal::config('biz_block_plugin.settings')->get('consultant_activity');
        //Get all query params
        $param = \Drupal::request()->query->all();
        //Get activity ID
        $id = isset($param['sid']) ? $param['sid'] : "0";
        $id = !empty($id) ? $id : $param['id'];
        //Get activity data
        $activity_response = GeneralFunctions::getSubmission($activity_endpoint, $id);
        if($activity_response['code'] !== 400){
          $activity_data = isset(json_decode($activity_response['message'])[0]) ? json_decode($activity_response['message'])[0] : [];
        }      
        $activity_email = isset($activity_data->mail) ? $activity_data->mail : "";
    
        if ($email !== $activity_email) {
          $response = new RedirectResponse('/system/403');
          $response->send();
        }
     
      }
    
      public function validateOwnerInHouseActivity() {
        $current_user = \Drupal::currentUser();
        $email = $current_user->getEmail();
        $edit_activity = $current_user->hasPermission('edit own in-house activity');
        $base_url =  \Drupal::config('biz_lobbyist_registration.settings')->get('base_url');
        $activity_endpoint = \Drupal::config('biz_block_plugin.settings')->get('in_house_activity');
        //Get all query params
        $param = \Drupal::request()->query->all();
        //Get activity ID
        $id = isset($param['sid']) ? $param['sid'] : "0";
        $id = !empty($id) ? $id : $param['id'];
        //Get activity data
        $activity_response = GeneralFunctions::getSubmission($activity_endpoint, $id);
        if($activity_response['code'] !== 400){
          $activity_data = isset(json_decode($activity_response['message'])[0]) ? json_decode($activity_response['message'])[0] : [];
        }    
        $activity_email = isset($activity_data->mail) ? $activity_data->mail : "";
        if ($email !== $activity_email) {
          $response = new RedirectResponse('/system/403');
          $response->send();
        }
      }
  }