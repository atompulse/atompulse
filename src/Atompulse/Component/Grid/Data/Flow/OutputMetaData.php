<?php
namespace Atompulse\Component\Grid\Data\Flow;

use Atompulse\Component\Grid\Data\Source\DataSourceInterface;

/**
 * Class OutputMetaData
 *
 * @package Atompulse\Component\Grid\Data\Flow
 * @author  Octavian Matei <octav@octav.name>
 * @since   07.08.2017
 */
class OutputMetaData implements OutputMetaDataInterface
{
    /**
     * @param \Atompulse\Component\Grid\Data\Source\DataSourceInterface $dataSource
     *
     * @return array
     */
    public function getOutputMetadata(DataSourceInterface $dataSource)
    {
        return [
            'have_to_paginate' => (bool) $dataSource->haveToPaginate(),
            'page'             => (int) $dataSource->getCurrentPageNumber(),
            'pages'            => (array) $dataSource->getPages(),
            'total'            => (int) $dataSource->getTotalRecords(),
            'total_available'  => (int) $dataSource->getCurrentNumberOfRecords(),
            'total_pages'      => (int) $dataSource->getTotalPages(),
        ];
    }
}
