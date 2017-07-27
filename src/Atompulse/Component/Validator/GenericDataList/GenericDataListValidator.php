<?php
namespace Atompulse\Component\Validator\GenericDataList;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenericDataListValidator
 * @package Atompulse\Component\Validator\GenericDataList
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class GenericDataListValidator extends ConstraintValidator
{
    /**
     * Service container
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;
    protected $dataList = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Validate the value against a list
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $this->prepareValidationDataList($constraint->parameter, $constraint->key);

        // validate only if the value is not empty
        if (!is_null($value) && strlen(trim($value)) > 0) {
            if (!in_array($value, $this->dataList)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%value%', $value)
                    //                ->setParameter('%parameter%', $constraint->parameter)
                    //                ->setParameter('%items_count%', count($this->dataList))
                    ->addViolation();
            }
        }
    }

    /**
     * Builds the validation list
     * @param $parameter
     * @param null $key
     */
    protected function prepareValidationDataList($parameter, $key = null)
    {
        $rawDataList = $this->container->getParameter($parameter);
        if (is_null($key)) {
            $this->dataList = $rawDataList;
        } else {
            foreach ($rawDataList as $item) {
                $this->dataList[] = $item[$key];
            }
        }
    }
}
