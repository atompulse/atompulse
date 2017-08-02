<?php
namespace Atompulse\Component\Grid\Data\Source;

use Atompulse\Component\Grid\DataGridInterface;
use Faker;

/**
 * Class MockDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class MockDataSource extends ArrayDataSource implements DataSourceInterface
{
    /**
     * Generate fake data based on the grid's config
     * @param DataGridInterface $dataGrid
     * @param int $nrRecords
     */
    public function generateMockData(DataGridInterface $dataGrid, int $nrRecords = 10)
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
}
