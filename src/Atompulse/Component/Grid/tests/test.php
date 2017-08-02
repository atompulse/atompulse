<?php
require_once "../vendor/autoload.php";

use Atompulse\Component\Grid\Configuration\GridConfiguration;
use Atompulse\Component\Grid\Data\Flow\Parameters;
use Atompulse\Component\Grid\DataGrid;
use Atompulse\Component\Grid\Data\Source;
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents('grid-config.yml'));
$data = json_decode(file_get_contents('data.json'), true);
//print_r($config);
//print_r($data);

$params = [];
$configuration = new GridConfiguration($config);
$parameters = new Parameters($params);

$grid = new DataGrid($configuration);
//print_r($grid->getMetaData());

$grid->setDataSource(new Source\ArrayDataSource($data));
$grid->setParameters($parameters);

print_r($grid->getData());
print_r($grid->getPagination());