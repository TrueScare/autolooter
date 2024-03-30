<?php

namespace App\Service;

use App\Struct\HeaderAction;
use App\Struct\HeaderActionGroup;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HeaderActionService
{
    private HeaderActionBuilder $builder;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;

    public function __construct(HeaderActionBuilder $builder, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->builder = $builder;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function getDefaultHeaderActions(): HeaderActionGroup
    {
        $builder = $this->addLegalActions($this->builder);
        return $builder->build();
    }

    public function getUserHeadeActions()
    {
        $builder = $this->builder
            ->addAction(
                new HeaderAction(
                    $this->translator->trans('item.random.label'),
                    $this->urlGenerator->generate('item_random')
                )
            )
            ->addGroup(
                new HeaderActionGroup(
                    $this->translator->trans('label.rarity'),
                    [
                        new HeaderAction(
                            $this->translator->trans('goto.rarity'),
                            $this->urlGenerator->generate('rarity_index')
                        ),
                        new HeaderAction(
                            $this->translator->trans('new.rarity'),
                            $this->urlGenerator->generate('api_rarity_edit')
                        )
                    ])
            )
            ->addGroup(
                new HeaderActionGroup(
                    $this->translator->trans('label.table'),
                    [
                        new HeaderAction(
                            $this->translator->trans('goto.table'),
                            $this->urlGenerator->generate('table_index')
                        ),
                        new HeaderAction(
                            $this->translator->trans('new.table'),
                            $this->urlGenerator->generate('api_table_edit')
                        )
                    ]
                )
            )
            ->addGroup(
                new HeaderActionGroup(
                    $this->translator->trans('label.item'),
                    [
                        new HeaderAction(
                            $this->translator->trans('goto.item'),
                            $this->urlGenerator->generate('item_index')
                        ),
                        new HeaderAction(
                            $this->translator->trans('new.item'),
                            $this->urlGenerator->generate('api_item_edit')
                        )
                    ]
                )
            );
        $builder = $this->addLegalActions($builder);
        return $builder->build();
    }

    public function getAdminHeaderActions()
    {
        $builder = $this->builder->addGroup(
            new HeaderActionGroup(
                $this->translator->trans('label.user'),
                [
                    new HeaderAction(
                        $this->translator->trans('goto.user'),
                        $this->urlGenerator->generate('admin_users')
                    ),
                    new HeaderAction(
                        $this->translator->trans('new.user'),
                        $this->urlGenerator->generate('api_admin_user_edit')
                    )
                ]
            )
        );
        return $builder->build();
    }

    private function addLegalActions(HeaderActionBuilder $builder)
    {
        $builder->addGroup(
            new HeaderActionGroup(
                $this->translator->trans('information.label'),
                [
                    new HeaderAction(
                        $this->translator->trans('information.impressum'),
                        $this->urlGenerator->generate('information_impressum')
                    )
                ]
            )
        );
        return $builder;
    }
}