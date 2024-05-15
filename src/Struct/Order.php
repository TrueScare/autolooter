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
    case LOGIN_ASC = 'loginAsc';
    case LOGIN_DESC = 'loginDesc';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return match ($this) {
            self::NAME_ASC => $translator->trans('order.name_asc', domain: 'enums', locale: $locale),
            self::NAME_DESC => $translator->trans('order.name_desc', domain: 'enums', locale: $locale),
            self::RARITY_ASC => $translator->trans('order.rarity_asc', domain: 'enums', locale: $locale),
            self::RARITY_DESC => $translator->trans('order.rarity_desc', domain: 'enums', locale: $locale),
            self::LOGIN_ASC => $translator->trans('order.login_asc', domain: 'enums', locale: $locale),
            self::LOGIN_DESC => $translator->trans('order.login_desc', domain: 'enums', locale: $locale),
        };
    }
}