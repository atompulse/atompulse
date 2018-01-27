<?php

use Symfony\Component\Workflow\DefinitionBuilder;

/**
 * Class Fusion
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionApi
{
    public function __construct(DefinitionBuilder $definitionBuilder)
    {
        $definition = $definitionBuilder->addPlaces(['draft', 'review', 'rejected', 'published'])
            // Transitions are defined with a unique name, an origin place and a destination place
            ->addTransition(new Transition('to_review', 'draft', 'review'))
            ->addTransition(new Transition('publish', 'review', 'published'))
            ->addTransition(new Transition('reject', 'review', 'rejected'))
            ->build();
    }

    /**
     * @return \Symfony\Component\Workflow\Workflow
     */
    public function getWorkflow(FilterWorkflow) : Symfony\Component\Workflow\Workflow
    {
        $workflow = new Symfony\Component\Workflow\Workflow();

        return $workflow;
    }
}
