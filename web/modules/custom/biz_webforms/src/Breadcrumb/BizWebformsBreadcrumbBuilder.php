<?php
  namespace Drupal\biz_webforms\Breadcrumb;

  use Drupal\Core\Breadcrumb\Breadcrumb;
  use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
  use Drupal\Core\Routing\RouteMatchInterface;
  use Drupal\Core\Link;
  use Drupal\Core\Breadcrumb\BreadcrumbManager;
  
  class BizWebformsBreadcrumbBuilder implements BreadcrumbBuilderInterface{

     /**
      * {@inheritdoc}
      */
     public function applies(RouteMatchInterface $attributes) {
/*
         $parameters = $attributes->getParameters()->all();
        

         if (!empty($parameters['node'])) {
            //\Drupal::logger('BizWebformsBreadcrumbBuilder')->notice(json_encode($parameters['node']->getType()));
            return $parameters['node']->getType() == 'page';
         }
*/
  return TRUE;

     }
  
     /**
      * {@inheritdoc}
      */
     public function build(RouteMatchInterface $route_match) {
/*
        $builder = new BreadcrumbBuilderInterface();
         \Drupal::logger('BizWebformsBreadcrumbBuilder22')->notice(json_encode($builder));
*/

        $breadcrumb = new Breadcrumb();
        if(\Drupal::currentUser()->id() == 0){
          $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        }
        // Get the node for the current page
        //$node = \Drupal::routeMatch()->getParameter('node');
        /*
        $node = $route_match->getParameter('node');
        \Drupal::logger('BizWebformsBreadcrumbBuilder22')->notice(json_encode($node));

        $current_uri = \Drupal::request()->getRequestUri(); 
       
        $breadcrumb->addLink(Link::createFromRoute('Page','entity.node.canonical', ['node' => 26, 'id'=>221]));
        $breadcrumb->addLink(Link::createFromRoute('Page','entity.node.canonical', ['node' => 26]));
*/
        //$breadcrumb->addCacheContexts(['route']);
        $breadcrumb = new Breadcrumb();
        $links = $breadcrumb->getLinks();
        //$links = array();
/*
        $links[] = Link::createFromRoute(t('Home2'), '<front>');
        $links[] = Link::createFromRoute(t('Home2'), '<front>');
*/
        
        return $breadcrumb->setLinks($links)->addCacheableDependency(0);

     
        
     }
  
  }