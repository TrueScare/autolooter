<?php

namespace App\Struct;

class HeaderActionGroup
{
    private String $title;
    private array $actions;

    /**
     * @param $title
     * @param array $actions
     */
    public function __construct($title, array $actions = [])
    {
        $this->title = $title;
        $this->actions = $actions;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle(String $title): void
    {
        $this->title = $title;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }

    public function addAction(HeaderAction $action): void
    {
        $this->actions[] = $action;
    }

    public function addGroup(HeaderActionGroup $group){
        $this->actions[] = $group;
    }

    public function getType(): string
    {
        return self::class;
    }
}