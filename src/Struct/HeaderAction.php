<?php

namespace App\Struct;

class HeaderAction
{
    private $label;
    private $route;

    /**
     * @param $label
     * @param $routeName
     */
    public function __construct($label, $routeName)
    {
        $this->label = $label;
        $this->route = $routeName;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label): void
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = $route;
    }


}