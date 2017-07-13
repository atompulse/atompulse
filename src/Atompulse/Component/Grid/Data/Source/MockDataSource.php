<?php
namespace Atompulse\Component\Grid\Data\Source;

use Faker;

/**
 * Class MockDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class MockDataSource implements  DataSourceInterface
{
    protected $pageMetaData = [
            'current_page_number' => 1,
            'total_records' => 1,
            'current_number_of_records' => 1,
            'total_pages' => 1,
            'pages' => 1,
            'has_pagination' => 1
        ];

    protected $data = [];

    /**
     * @param mixed $query
     * @param array $pagination
     */
    public function setup($query, $pagination = ['page' => 1, 'page_size' => 10])
    {
    }

    /**
     * @param $data
     */
    public function setMockData($data = [], $pageMetaData = [])
    {
        if (count($data)) {
            $this->data = $data;
        }
        if (count($pageMetaData)) {
            $this->pageMetaData = $pageMetaData;
        }
    }

    /**
     * Generate fake data based on the grid's config
     * @param null $dataGrid
     * @param int $nrRecords
     */
    public function generateMockData($dataGrid = null, $nrRecords = 10)
    {
        $gridMetaData = $dataGrid->getMetaData();

        $mockData = [];

        for ($i = 0; $i < $nrRecords; $i++) {
            $entry = new \stdClass();
            foreach ($gridMetaData['header'] as $fieldSettings) {
                $idx = $gridMetaData['columnsOrderMap']['pos2name'][$fieldSettings['aTargets'][0]];
                $entry->$idx = $this->generateMockValue($fieldSettings['fType'], $fieldSettings['fScope']);
            }
            $mockData[] = $entry;
        }

        $this->data = $mockData;
    }

    /**
     * @param $fieldType
     * @param $fieldScope
     * @return int|null|string
     */
    protected function generateMockValue($fieldType, $fieldScope)
    {
        $faker = Faker\Factory::create();
        $value = null;

        if (is_null($fieldScope)) {
            switch ($fieldType) {
                case 'number' :
                    $value = rand(1, 1000);
                    break;
                case 'string' :
                    $value = $this->generateRandomString(10);
                    break;
            }
        } else {
            switch ($fieldScope) {
                case 'name' :
                    $value = $faker->name;
                    break;
                case 'email' :
                    $value = $faker->email;
                    break;
                case 'phone' :
                    $value = $faker->phoneNumber;
                    break;
                case 'date' :
                    $value = $faker->date();
                    break;
                case 'address' :
                    $value = $faker->address;
                    break;
                case 'state' :
                    $value = $faker->boolean();
                    break;
            }
        }

        return $value;
    }

    /**
     * @param int $length
     * @return string
     */
    private function generateRandomString ($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Get the data from the source
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Current page number
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->pageMetaData['current_page_number'];
    }

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->pageMetaData['total_records'];
    }

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords()
    {
        return $this->pageMetaData['current_number_of_records'];
    }

    public function getTotalPages()
    {
        return $this->pageMetaData['total_pages'];

    }

    public function getPages()
    {
        return $this->pageMetaData['pages'];

    }

    public function haveToPaginate()
    {
        return $this->pageMetaData['has_pagination'];

    }

}