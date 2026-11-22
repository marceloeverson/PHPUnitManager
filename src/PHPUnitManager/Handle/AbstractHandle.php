<?php

namespace PHPUnitManager\Handle;

abstract class AbstractHandle
{
    protected array $params  = [];
    protected array $scripts = [];
    
    public function __construct(array $params, array $scripts)
    {
        $this->params  = $params;
        $this->scripts = $scripts;
        
    }
    abstract public function run();
}

