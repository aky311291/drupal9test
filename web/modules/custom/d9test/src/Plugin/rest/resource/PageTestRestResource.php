<?php

namespace Drupal\d9test\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
#use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "page_test_rest_resource",
 *   label = @Translation("Page test rest resource"),
 *   uri_paths = {
 *     "canonical" = "/page_json/{apikey}/{nid}"
 *   }
 * )
 */
class PageTestRestResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->logger = $container->get('logger.factory')->get('d9test');
        $instance->currentUser = $container->get('current_user');
        return $instance;
    }

    /**
     * Responds to GET requests.
     *
     * @param string $payload
     *
     * @return \Drupal\rest\ResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get($apikey, $nid)
    {
        $payloadNodData = "";
        # Load the site configuration data
        $site_config = \Drupal::config('system.site');

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException('Access denied');
        } elseif ($site_config->get('siteapikey') != $apikey) {
            throw new AccessDeniedHttpException('Access denied for API key');
        } elseif (!empty($nid)) {
            // $nodeData =  \Drupal\node\Entity\Node::loadMultiple([$nid]);
            $values = [
                'nid' => $nid,
                'type' => 'page',
            ];

            // Get the node data.
            $nodeData = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadByProperties($values);
            if (!is_array($nodeData) || count($nodeData) < 1) {
                throw new AccessDeniedHttpException('Access denied for content');
            }
            $payloadNodData = $nodeData;
        }

        $payload = ["apikey" => $apikey, "nid" => $nid, "data" => $payloadNodData];
        return new ResourceResponse($payload, 200);
    }
}
