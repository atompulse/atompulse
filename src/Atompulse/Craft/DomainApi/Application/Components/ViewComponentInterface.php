<?php

/**
 * Interface ApplicationTransitionInterface
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface ViewComponentInterface
{
    public function input(ViewRequestInterface $viewRequest) : ViewComponentEventInterface;

    public function output() : ViewResponseInterface;
}
