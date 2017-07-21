<?php
namespace Atompulse\FusionBundle\Services;

use Atompulse\FusionBundle\Compiler\Refiner\SimpleRefiner;
use Atompulse\Component\Data;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Atompulse Fusion Data Manager
 * @package Atompulse\FusionBundle\Services
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionDataManager
{
    /**
     * @var string
     */
    private $version = '0.3';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Controller\Controller
     */
    protected $controller = null;

    /**
     * @var bool
     */
    protected $isQualifiedRequest = false;

    /**
     * Data Container
     * @var array
     */
    protected $dataContainer = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Determine if the controller is qualified for data injection
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest() && $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $controller = $event->getController()[0];
            if ($controller instanceof \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController) {
                return ;
            }
            $this->controller = $controller;
            $this->isQualifiedRequest = true;

            $this->setData('version', $this->version, 'Fusion');
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->isQualifiedRequest && count($this->dataContainer)) {
            $response->setContent($this->addDataToResponse($response->getContent()));
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        $isAjaxRequest = $request->isXmlHttpRequest();

        if (!$isAjaxRequest) {
            $this->isQualifiedRequest = true;
        }
    }

    /**
     * Check if the current controller action is qualified for data transmission
     * @note This method is intended to be used as a service method for the container
     *
     * @return bool
     */
    public function isControllerActionQualifiedForData()
    {
        return $this->isQualifiedRequest;
    }

    /**
     * Add data with $name and $value to js
     * @param $name
     * @param $value
     * @param bool|false $scope
     */
    public function setData($name, $value, $scope = false)
    {
        // resolve scope: if none given the current controller name will be used
        $scope = $scope ? $scope : Data\Transform::getControllerName(get_class($this->controller));

        $this->dataContainer[$scope][$name] = $value;
    }

    /**
     * Return data that will be added to js
     * @param bool|false $name
     * @param bool|false $scope
     * @return array
     */
    public function getData($name = false, $scope = false)
    {
        if ($name) {
            $scope = $scope ? $scope : Data\Transform::getControllerName(get_class($this->controller));
            return $this->dataContainer[$scope][$name];
        } else {
            return $this->dataContainer;
        }
    }

    /**
     * Retrieve the controller that will be used for data injection
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Perform the data injection
     * @param $content
     * @return mixed
     */
    protected function addDataToResponse($content)
    {
        $preparedJsData = $this->transformDataForJs($this->dataContainer);
        //print '<pre>';print_r($preparedJsData);die;
        $params = ['data' => $preparedJsData];
        $scriptContent = SimpleRefiner::refine($this->container->get('twig')->render('add-js-data.html.twig', $params));

        // perform injection tag replacement

        $content = str_replace('<!--@fusion_inject_data-->', $scriptContent, $content);

        return $content;
    }

    /**
     * Transform PHP data structure to optimized
     * simple key/value JS data structure
     * @param array $phpData
     * @return array
     */
    protected function transformDataForJs($phpData)
    {
        $transformedData = [];
        foreach ($phpData as $varScope => $varData) {
            $transformedData[$varScope] = json_encode($varData, JSON_NUMERIC_CHECK|JSON_PARTIAL_OUTPUT_ON_ERROR);
        }

        return $transformedData;
    }

}