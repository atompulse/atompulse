<?php
namespace Atompulse\Bundle\RadBundle\Services\Routing;

use Atompulse\Bundle\FusionBundle\Services\FusionDataManager;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class RoutesExportService
 *
 * @package Atompulse\Bundle\RadBundle\Services\Routing
 *
 * @author  Petru Cojocar <petru.cojocar@gmail.com>
 */
final class RoutesExportService
{
    const CACHE_FOLDER = 'rad-bundle';

    /** @var string */
    protected $cacheDir = null;

    /** @var \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor */
    protected $extractor = null;

    /** @var \Atompulse\Bundle\FusionBundle\Services\FusionDataManager */
    protected $fusionDataManager = null;

    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request = null;

    /**
     * RoutesExportService constructor.
     */
    public function __construct(ExposedRoutesExtractor $extractor, FusionDataManager $fusionDataManager, RequestStack $requestStack, $cacheDir)
    {
        $this->cacheDir = $cacheDir;
        $this->extractor = $extractor;
        $this->fusionDataManager = $fusionDataManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->fusionDataManager->isControllerActionQualifiedForData()) {
            $this->fusionDataManager->setData('Routing', $this->getExposedRoutes(), 'Application');
        }
    }

    /**
     * Generate cache file.
     *
     * @param array $context
     *
     * @return string
     */
    protected function getCacheFile(array $context): string
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . self::CACHE_FOLDER;

        if (!file_exists($cachePath)) {
            mkdir($cachePath);
        }

        $hash = \sha1(implode('_', $context));

        return $cachePath . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    /**
     * Get Exposed Routes.
     *
     * @return array|mixed
     */
    protected function getExposedRoutes()
    {
        $context = [
            'base_url' => $this->extractor->getBaseUrl(),
            'host'     => $this->extractor->getHost(),
            'locale'   => $this->request->getLocale(),
            'prefix'   => $this->extractor->getPrefix($this->request->getLocale()),
            'scheme'   => $this->extractor->getScheme(),
        ];

        $cacheFile = $this->getCacheFile($context);

        $cache = new ConfigCache($cacheFile, false);

        if ($cache->isFresh()) {
            return unserialize(file_get_contents($cache->getPath()));
        }

        $routing = [
            'context' => $context,
            'routes'  => $this->prepareRoutes(),
        ];

        $cache->write(serialize($routing), $this->extractor->getResources());

        return $routing;
    }

    /**
     * Prepare routes to be exposed.
     *
     * @return array
     */
    protected function prepareRoutes(): array
    {
        $preparedRoutes = [];

        foreach ($this->extractor->getRoutes() as $name => $route) {
            $compiledRoute = $route->compile();

            $defaults = \array_intersect_key(
                $route->getDefaults(),
                \array_fill_keys($compiledRoute->getVariables(), null)
            );

            if (!isset($defaults['_locale']) && \in_array('_locale', $compiledRoute->getVariables())) {
                $defaults['_locale'] = $this->request->getLocale();
            }

            $preparedRoutes[$name] = [
                'defaults'     => $defaults,
                'requirements' => $route->getRequirements(),
                'tokens'       => $compiledRoute->getTokens(),
                'hosttokens'   => \method_exists($compiledRoute, 'getHostTokens')
                    ? $compiledRoute->getHostTokens()
                    : [],
            ];
        }

        return $preparedRoutes;
    }
}
