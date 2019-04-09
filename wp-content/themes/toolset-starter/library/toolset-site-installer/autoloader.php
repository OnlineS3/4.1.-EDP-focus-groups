<?php

/**
 * Autoloader uses classmap
 *
 * @param $class
 */
class TT_Autoloader
{
    /**
     * @var array
     */
    private $classmap;

    public function __construct($path_to_classmap)
    {
        if (file_exists($path_to_classmap)) {
            $this->classmap = include($path_to_classmap);
            spl_autoload_register(array($this, 'autoload'));
        }
    }

    /**
     * autoload function using a classmap
     */
    private function autoload($class)
    {
        if (array_key_exists($class, $this->classmap)) {
            include_once($this->classmap[$class]);
        }
    }
}