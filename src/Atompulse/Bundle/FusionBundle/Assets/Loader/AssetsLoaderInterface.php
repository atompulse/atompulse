<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Loader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface AssetsLoaderInterface
 * @package Atompulse\Bundle\FusionBundle\Assets\Loader
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface AssetsLoaderInterface
{
    /**
     * Add assets to response
     * @param Response $response
     * @param Request $request
     * @return mixed
     */
    public function addAssetsToResponse(Response $response, Request $request);
}
