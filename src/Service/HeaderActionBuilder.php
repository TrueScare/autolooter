<?php

namespace App\Service;

use App\Struct\HeaderAction;
use App\Struct\HeaderActionGroup;
use Symfony\Contracts\Translation\TranslatorInterface;

class HeaderActionBuilder
{
    private HeaderActionGroup $actions;
    private TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        $this->actions = new HeaderActionGroup(
            $this->translator->trans('menu')
        );
    }

    public function setTitle(String $title): static
    {
        $this->actions->setTitle($title);
        return $this;
    }

    public function addAction(HeaderACtion $action): HeaderActionBuilder
    {
        $this->actions->addAction($action);
        return $this;
    }

    public function addGroup(HeaderActionGroup $group): HeaderActionBuilder
    {
        $this->actions->addGroup($group);
        return $this;
    }

    public function build(): HeaderActionGroup
    {
        return $this->actions;
    }
}