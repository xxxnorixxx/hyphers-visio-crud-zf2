<?php
namespace VisioCrudModeler\Descriptor\Db;

use VisioCrudModeler\Descriptor\AbstractDataSourceDescriptor;
use Zend\Db\Adapter\Adapter;
use VisioCrudModeler\Exception\DataSetNotFound;
use VisioCrudModeler\Descriptor\ListGeneratorInterface;

/**
 * descriptor for database sources
 *
 * @author bweres01
 *        
 * @method \VisioCrudModeler\DataSource\DbDataSource getDataSource
 */
class DbDataSourceDescriptor extends AbstractDataSourceDescriptor implements ListGeneratorInterface
{

    protected $tableTypes = array(
        'BASE TABLE' => 'table',
        'VIEW' => 'view'
    );

    protected $tablesDescriptionStatement = 'SELECT * FROM information_schema.TABLES it WHERE it.TABLE_SCHEMA = :database';

    protected $viewsDescriptionStatement = 'SELECT * FROM information_schema.VIEWS iv WHERE iv.TABLE_SCHEMA = :database';

    protected $columnsDescriptionStatement = 'SELECT * FROM information_schema.COLUMNS ic WHERE ic.TABLE_SCHEMA = :database';

    protected $referenceDescriptionStatement = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE kcu WHERE kcu.TABLE_SCHEMA = :database and kcu.REFERENCED_COLUMN_NAME IS NOT NULL';

    /**
     * holds dataset descriptors object instances
     *
     * @var \ArrayObject
     */
    protected $dataSetDescriptors = null;

    /**
     * constructor accepting DbDataSource instance
     *
     * @param DbDataSource $dataSource            
     */
    public function __construct(Adapter $adapter, $name)
    {
        $this->adapter = $adapter;
        $this->setName($name);
        $this->dataSetDescriptors = new \ArrayObject(array());
    }
    
    /*
     * (non-PHPdoc) @see \VisioCrudModeler\DataSource\Descriptor\AbstractDataSourceDescriptor::describe()
     */
    protected function describe()
    {
        if (! $this->definitionResolved) {
            $this->describeTables();
            $this->describeViews();
            $this->describeColumns();
            $this->describeReferences();
            $this->definitionResolved = true;
        }
        return $this;
    }

    /**
     * describes tables in database
     */
    protected function describeTables()
    {
        $result = $this->getAdapter()
            ->createStatement($this->tablesDescriptionStatement)
            ->execute(array(
            'database' => $this->getName()
        ));
        if ($result->isQueryResult()) {
            foreach ($result as $row) {
                $tableDefinition = $this->createTableDefinition($row);
                $this->definition[$tableDefinition['name']] = $tableDefinition;
            }
        }
    }

    /**
     * creates tables definition
     *
     * @param array $informationSchemaRow            
     * @return array
     */
    protected function createTableDefinition(array $informationSchemaRow)
    {
        return array(
            'type' => $this->tableTypes[$informationSchemaRow['TABLE_TYPE']],
            'name' => $informationSchemaRow['TABLE_NAME'],
            'updateable' => true,
            'fields' => array()
        );
    }

    /**
     * describes views in database
     */
    protected function describeViews()
    {
        $result = $this->getAdapter()
            ->createStatement($this->viewsDescriptionStatement)
            ->execute(array(
            'database' => $this->getName()
        ));
        if ($result->isQueryResult()) {
            foreach ($result as $row) {
                if ($row['IS_UPDATEABLE'] == 'NO') {
                    $this->definition[$row['TABLE_NAME']]['updateable'] = false;
                }
            }
        }
    }

    /**
     * describes columns in database
     */
    protected function describeColumns()
    {
        $result = $this->getAdapter()
            ->createStatement($this->columnsDescriptionStatement)
            ->execute(array(
            'database' => $this->getName()
        ));
        if ($result->isQueryResult()) {
            foreach ($result as $row) {
                $fieldDescription = array(
                    'name' => $row['COLUMN_NAME'],
                    'type' => $row['DATA_TYPE'],
                    'character_maximum_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                    'numeric_precision' => $row['NUMERIC_PRECISION'],
                    'numeric_scale' => $row['NUMERIC_SCALE'],
                    'null' => ($row['IS_NULLABLE'] == 'YES') ? true : false,
                    'default' => $row['COLUMN_DEFAULT'],
                    'key' => $row['COLUMN_KEY'],
                    'reference' => false
                );
                $this->definition[$row['TABLE_NAME']]['fields'][$row['COLUMN_NAME']] = $fieldDescription;
            }
        }
    }

    /**
     * describes columns in database
     */
    protected function describeReferences()
    {
        $result = $this->getAdapter()
            ->createStatement($this->referenceDescriptionStatement)
            ->execute(array(
            'database' => $this->getName()
        ));
        if ($result->isQueryResult()) {
            foreach ($result as $row) {
                $referenceDescription = array(
                    'dataset' => $row['REFERENCED_TABLE_NAME'],
                    'field' => $row['REFERENCED_COLUMN_NAME']
                );
                $this->definition[$row['TABLE_NAME']]['fields'][$row['COLUMN_NAME']]['reference'] = $referenceDescription;
            }
        }
    }

    /**
     * returns DataSet descriptor
     *
     * @throws \VisioCrudModeler\Exception\DataSetNotFound
     * @return \VisioCrudModeler\Descriptor\Db\DbDataSetDescriptor
     */
    public function getDataSetDescriptor($dataSetName)
    {
        $this->describe();
        if (! array_key_exists($dataSetName, $this->definition)) {
            throw new DataSetNotFound("dataSet '" . $dataSetName . "' don't exists in '" . $this->name . "'");
        }
        if (! $this->dataSetDescriptors->offsetExists($dataSetName)) {
            $this->dataSetDescriptors->offsetSet($dataSetName, new DbDataSetDescriptor($this, $this->definition[$dataSetName]));
        }
        return $this->dataSetDescriptors->offsetGet($dataSetName);
    }

    /**
     * list generator
     *
     * keys are DataSet names, value is DbDataSetDescriptor objects
     *
     * @return Generator
     */
    public function listGenerator()
    {
        foreach ($this->listDataSets() as $dataSetName) {
            yield $dataSetName => $this->getDataSetDescriptor($dataSetName);
        }
    }
}