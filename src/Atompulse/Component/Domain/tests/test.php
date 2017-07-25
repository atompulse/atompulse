<?php

require_once "../vendor/autoload.php";

use Atompulse\Component\Domain\tests\model;

$dateAdded = new \DateTime('2017-07-07');
$category = new model\CategoryData(['name' => 'Sedan']);

$data = [
    'id' => null,
//    'date_added' => $dateAdded, // complex type
    'date_added' => '2017-07-07', // string
//    'date_added' => null,
    'category' => $category,
    'products' => [
        0 => [
            "brand" => "Volvo",
            "name" => "85",
            "price" => 10000,80,
        ],
        1 => [
            "id" => "1",
            "brand" => "Mercedes",
            "name" => "SLK",
            "price" => "20000",
        ],
        2 => new model\ProductData([
            "id" => 2,
            "brand" => "Tesla",
            "name" => "Model 3",
            "price" => 30.000,
        ]),
    ]
];

$domainData = new model\DomainData($data);

print "Test multi dimensional access\n";
var_dump($domainData->products[1]->brand == "Mercedes");
print "\n\n";
print "Test complex type\n";
var_dump($domainData->getDateAdded() == '2017-07-07');
print "\n\n";
print "Test access complex object\n";
var_dump($domainData->category == $category);
print "\n\n";
print "Test list of complex object collection\n";
var_dump($domainData->getProducts());
print "\n\n";
print "Test normalizeData\n";
var_dump($domainData->normalizeData());
print "\n\n";
print "Test toArray\n";
var_dump($domainData->toArray());
print "\n\n";
