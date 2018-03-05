<?php

declare(strict_types=1);

require_once "/Users/petru.cojocar/workspace/atompulse/src/Atompulse/Component/Domain/vendor/autoload.php";

use Atompulse\Component\Domain\Data\DataContainer;

/**
 * Class test
 * @property array foo
 * @property array unit
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class test {

    public $bar = [];

    use DataContainer;

    public function __construct()
    {
        $this->defineProperty('x', ['string']);
        $this->defineProperty('foo', ['array']);
        $this->defineProperty('unit', ['array'], ['m', 'mm']);
    }

}

$b = null;
$b[] = 'asss';

print_r($b);

$t = new test();
//$t->foo = ['aaa'];
$t->foo[] = 'bbb';
$t->foo[] = 'ccc';

//$t->unit[] = 'plm';

print_r($t->toArray());
print_r($t->normalizeData());
