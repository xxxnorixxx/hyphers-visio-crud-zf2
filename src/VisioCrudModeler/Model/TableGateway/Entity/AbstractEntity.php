<?php
/**
 * Description of AbstractTable
 *
 * @author  Piotr Duda (dudapiotrek@gmail.com)
 */

namespace VisioCrudModeler\Model\TableGateway\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use VisioCrudModeler\Hydrator\ClassMethodsPublicProperty;

abstract class AbstractEntity implements InputFilterAwareInterface
{
    /**
     *
     * @var InputFilter 
     */
    protected $inputFilter;

    
    
    /**
     * Magic method to get or set variable in object
     * Variables are storage in data array
     * 
     * @param string $name
     * @param array $arguments
     * @return type
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $setOrGet = lcfirst(substr($name, 0,3));
        $nameOfColumn = lcfirst(substr($name, 3));
        
        
        if($setOrGet != 'get' && $setOrGet != 'set'){
            throw new \Exception('Method must start with set or get prefix ');
        }
        $nameOfColumn = strtolower(  preg_replace('/([A-Z])/', '_$1', $nameOfColumn) ) ;
        
        if($setOrGet == 'set'){
            $this->$nameOfColumn = $arguments[0];
        }
       
    }
    
    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $hydrator = new \Zend\Stdlib\Hydrator\ClassMethods();
        $hydrator->hydrate($data, $this);
    }
    
    
    /**
     * Return public an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $hydrator = new ClassMethodsPublicProperty();
        return $hydrator->extract($this);
    }

    /**
     * 
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * 
     * @return InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;        
        }
        return $this->inputFilter;
    }
    
}