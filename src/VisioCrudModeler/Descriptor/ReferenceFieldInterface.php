<?php
namespace VisioCrudModeler\Descriptor;

interface ReferenceFieldInterface
{

    /**
     * returns referenced DataSet name
     *
     * @return string
     */
    public function referencedDataSetName();

    /**
     * returns referenced Field name
     *
     * @return string
     */
    public function referencedFieldName();
}