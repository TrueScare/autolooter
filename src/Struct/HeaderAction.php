<?php

namespace App\Struct;

class HeaderAction
{
    private $label;
    private $route;
    private array $subactions;

    /**
     * @param $label
     * @param $routeName
     * @param array $subactions
     */
    public function __construct($label, $routeName, $subactions = [])
    {
        $this->label = $label;
        $this->route = $routeName;
        $this->subactions = $subactions;
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

    public function getSubactions(): array
    {
        return $this->subactions;
    }

    public function setSubactions(array $subactions): void
    {
        $this->subactions = $subactions;
    }

    public function getType(): string
    {
        return self::class;
    }
}