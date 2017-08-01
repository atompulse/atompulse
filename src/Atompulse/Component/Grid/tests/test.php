<?php
require_once "../vendor/autoload.php";

use Atompulse\Component\Grid\Configuration\GridConfiguration;
use Atompulse\Component\Grid\Data\Flow\Parameters;
use Atompulse\Component\Grid\DataGrid;
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents('grid-config.yml'));
print_r($config);

$params = [];
$configuration = new GridConfiguration($config);
$parameters = new Parameters($params);


$grid = new DataGrid($configuration);
$grid->setParameters($parameters);

print_r($grid->getMetaData());
var_dump($grid->getGridData());
var_dump($grid->getPagination());