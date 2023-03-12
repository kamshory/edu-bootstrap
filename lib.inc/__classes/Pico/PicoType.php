<?php
namespace Pico;

class PicoType {
    /**
     * Get type of variable or object
     * @param mixed $object
     * @return mixed
     */
    public static function getType($object)
    {
        return gettype($object);
    }

    /**
     * Get value of variable or object
     * @param mixed $object
     * @param string $type
     * @return mixed
     */
    public static function valueOf($object, $type = null)
    {
        if($type === null)
        {
            $type = \Pico\PicoType::getType($object);
        }
        if($type == "boolean")
        {
            return $object?true:false;
        }
        if($type == "double")
        {
            return (double) $object;
        }
        if($type == "integer")
        {
            return (int) $object;
        }
        return $object;
    }
    
}