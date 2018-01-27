<?php

use Symfony\Component\Workflow\Registry;

/**
 * Class WorkflowEngine
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class WorkflowEngine
{
    public function __construct()
    {
        $registry = new Registry();
        $registry->add($blogWorkflow, BlogPost::class);
        $registry->add($newsletterWorkflow, Newsletter::class);
    }


}
