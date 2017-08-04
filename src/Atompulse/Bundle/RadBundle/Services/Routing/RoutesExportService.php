<?php
namespace Atompulse\Bundle\RadBundle\Services\Routing;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\ConfigCache;

/**
 * Class RoutesExportService
 * @package Atompulse\Bundle\RadBundle\Services\Routing
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class RoutesExportService
{
    use ContainerAwareTrait;

    /**
     * @var \Symfony\Component\HttpFoundation\Request;
     */
    protected $request = null;

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->container->get('fusion.data.manager')->isControllerActionQualifiedForData()) {
            $this->request = $event->getRequest();
            $this->container->get('fusion.data.manager')->setData('Routing', $this->getExposedRoutes(), 'Application');
        }
    }

    /**
     * Get Exposed Routes
     * @return array|mixed
     */
    protected function getExposedRoutes()
    {
        $debug = false;
        $extractor = $this->container->get('fos_js_routing.extractor');

        $cacheFile = $extractor->getCachePath($this->request->getLocale());
        $cache = new ConfigCache($cacheFile, $debug);

        if (!$cache->isFresh()) {
            $preparedRoutes = [];
            foreach ($extractor->getRoutes() as $name => $route) {
                $compiledRoute = $route->compile();
                $defaults      = array_intersect_key(
                    $route->getDefaults(),
                    array_fill_keys($compiledRoute->getVariables(), null)
                );

                if (!isset($defaults['_locale']) && in_array('_locale', $compiledRoute->getVariables())) {
                    $defaults['_locale'] = $this->request->getLocale();
                }

                $preparedRoutes[$name] = [
                    'tokens'       => $compiledRoute->getTokens(),
                    'defaults'     => $defaults,
                    'requirements' => $route->getRequirements(),
                    'hosttokens'   => method_exists($compiledRoute, 'getHostTokens') ? $compiledRoute->getHostTokens() : array(),
                ];
            }

            $routing = [
                'context' => [
                    'base_url' => $extractor->getBaseUrl(),
                    'prefix' => $extractor->getPrefix($this->request->getLocale()),
                    'host' => $extractor->getHost(),
                    'scheme' => $extractor->getScheme(),
                    'locale' => $this->request->getLocale()
                ],
                'routes' => $preparedRoutes,
            ];

            $cache->write(serialize($routing), $extractor->getResources());
        } else {
            $routing = unserialize(file_get_contents($cache->getPath()));
        }

        return $routing;
    }

}
