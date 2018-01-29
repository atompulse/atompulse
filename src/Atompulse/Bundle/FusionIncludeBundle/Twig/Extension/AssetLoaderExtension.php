<?php

namespace Atompulse\Bundle\FusionIncludeBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;

/**
 * Class AssetLoaderExtension
 * @package App\Twig\Extension\AssetLoader
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AssetLoaderExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return array(
            new \Twig_Function('fusion_include', [$this, 'fusionInclude']),
        );
    }

    public function useAsset()
    {

    }


}
