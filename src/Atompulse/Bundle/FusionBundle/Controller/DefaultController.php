<?php

namespace Atompulse\FusionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FusionBundle:Default:index.html.twig', array('name' => $name));
    }
}
