<?php

namespace App\Struct;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Order: string implements TranslatableInterface
{
    case NAME_ASC = 'nameAsc';
    case NAME_DESC = 'nameDesc';
    case RARITY_ASC = 'rarityAsc';
    case RARITY_DESC = 'rarityDesc';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return match($this){
            self::NAME_ASC => $translator->trans('enum.order.name_asc', locale: $locale),
            self::NAME_DESC => $translator->trans('enum.order.name_desc', locale: $locale),
            self::RARITY_ASC => $translator->trans('enum.order.rarity_asc', locale: $locale),
            self::RARITY_DESC => $translator->trans('enum.order.rarity_desc', locale: $locale),
        };
    }
}