<?php

/**
 * Interface ApplicationStateInterface
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface ActionComponentInterface
{
    public function input(ActionRequestInterface $actionRequest) : ActionComponentEventInterface;

    public function output() : ActionResponseInterface;
}
