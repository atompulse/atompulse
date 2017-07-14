<?php
namespace Atompulse\FusionBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Environment;
use \Twig_Loader_String;

/**
 * Class Fusion Twig Extension
 * @package Atompulse\FusionBundle\Twig
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionTwigExtension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $path = $this->container->get('kernel')->locateResource('@FusionBundle/Resources/views', 'FusionBundle');
        $this->container->get('twig')->getLoader()->addPath($path);
    }

    public function getName()
    {
        return 'fusion_twig_extension';
    }

    public function getFunctions()
    {
        $globalOptions = ['is_safe' => ['html']];

        $functions = [
//            $this->setupInitFunction($globalOptions),
//            $this->setupLoadFunction($globalOptions),
//            $this->setupInjectFunction($globalOptions),
            $this->setupFusionKernel($globalOptions),
            $this->setupFusionData($globalOptions),
            $this->setupFusionIncludeCss($globalOptions),
            $this->setupFusionIncludeJs($globalOptions),
            $this->setupFusionIncludeAngular($globalOptions),
        ];

        return $functions;
    }

//    /**
//     * APFusion js vendors initialization
//     * TODO: handle environments for optimization and loading specific minified/debug versions
//     * @return string
//     */
//    public function handleInit()
//    {
//        return $this->container->get('twig')->render('extension/init.html.twig');
//    }
//
//    /**
//     * Asset loading
//     * @param string $assetGroup
//     * @return string|null
//     */
//    public function handleLoad($assetGroup)
//    {
//        $this->container->get('Atompulse_fusion.asset_manager')->loadGroup($assetGroup);
//    }
//
//    /**
//     * Inject an asset group into HTML
//     * @param string $assetGroup
//     * @return string
//     */
//    public function handleInject($assetGroup)
//    {
//        $this->container->get('Atompulse_fusion.asset_manager')->injectGroup($assetGroup);
//
//        return "<!--@inject $assetGroup-->";
//    }
//
//    protected function setupLoadFunction($globalOptions)
//    {
//        $function = new \Twig_SimpleFunction('Atompulse_fusion_load', [$this, 'handleLoad'], $globalOptions);
//
//        return $function;
//    }
//
//    protected function setupInitFunction($globalOptions)
//    {
//        $function = new \Twig_SimpleFunction('Atompulse_fusion_init',   [$this, 'handleInit'],   $globalOptions);
//
//        return $function;
//    }
//
//    protected function setupInjectFunction($globalOptions)
//    {
//        $function = new \Twig_SimpleFunction('fusion_inject', [$this, 'handleInject'], $globalOptions);
//
//        return $function;
//    }

    protected function setupFusionKernel($globalOptions)
    {
        $function = new \Twig_SimpleFunction('fusion_kernel', [$this, 'handleFusionKernel'], $globalOptions);

        return $function;
    }

    public function handleFusionKernel()
    {
        return "<!--@fusion_inject_kernel-->";
    }

    protected function setupFusionData($globalOptions)
    {
        $function = new \Twig_SimpleFunction('fusion_data', [$this, 'handleFusionData'], $globalOptions);

        return $function;
    }

    public function handleFusionData()
    {
        return "<!--@fusion_inject_data-->";
    }

    protected function setupFusionIncludeCss($globalOptions)
    {
        $function = new \Twig_SimpleFunction('fusion_include_css', [$this, 'handleFusionIncludeCss'], $globalOptions);

        return $function;
    }

    public function handleFusionIncludeCss()
    {
        return "<!--@fusion_inject_css-->";
    }

    protected function setupFusionIncludeJs($globalOptions)
    {
        $function = new \Twig_SimpleFunction('fusion_include_js', [$this, 'handleFusionIncludeJs'], $globalOptions);

        return $function;
    }

    public function handleFusionIncludeJs()
    {
        return "<!--@fusion_inject_js-->";
    }

    protected function setupFusionIncludeAngular($globalOptions)
    {
        $options = array_merge(['needs_context' => true], $globalOptions);

        $function = new \Twig_SimpleFunction('fusion_include_ng', [$this, 'handleFusionIncludeAngular'], $options);

        return $function;
    }

    /**
     * @param array $contextParams all current context params ~ global params
     * @param string $template
     * @param string $angularId
     * @param array $params
     * @return mixed
     */
    public function handleFusionIncludeAngular($contextParams, $template, $angularId, $params = [])
    {
        $params = [
            'template' => $template,
            'angular_id' => $angularId,
            'params' => array_merge($contextParams, $params),
        ];

        return $this->container->get('twig')->render('extension/include-angular.html.twig', $params);
    }
}