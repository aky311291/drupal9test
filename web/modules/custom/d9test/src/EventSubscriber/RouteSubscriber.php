<?php

namespace Drupal\d9test\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 */
class RouteSubscriber extends RouteSubscriberBase
{

    /**
     * Constructs a new RouteSubscriber object.
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function alterRoutes(RouteCollection $collection)
    {
        if ($route = $collection->get('system.site_information_settings'))
            $route->setDefault('_form', 'Drupal\d9test\Form\ExtendedSiteInformationForm');
    }
}
