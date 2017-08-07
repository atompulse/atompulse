<?php
namespace Atompulse\Component\Grid\Data\Flow;

use Atompulse\Component\Grid\Data\Source\DataSourceInterface;

/**
 * Interface OutputMapper
 *
 * @package Atompulse\Component\Grid\Data\Flow
 * @author  Octavian Matei <octav@octav.name>
 * @since   07.08.2017
 */
interface OutputMetaDataInterface
{
    /**
     * @param \Atompulse\Component\Grid\Data\Source\DataSourceInterface $dataSource
     *
     * @return array
     */
    public function getOutputMetadata(DataSourceInterface $dataSource);
}
