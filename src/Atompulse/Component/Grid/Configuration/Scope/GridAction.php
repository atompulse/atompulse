<?php
namespace Atompulse\Component\Grid\Configuration\Scope;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class GridAction
 * @package Atompulse\Component\Grid\Configuration\Scope
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class GridAction implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array $action
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException
     */
    public function __construct(array $action = [])
    {
        $this->validProperties = [
            'with' => 'string|null',
            'render' => 'string|null'
        ];

        if ($action !== null) {
            return $this->fromArray($action);
        }

        return $this;
    }

}
