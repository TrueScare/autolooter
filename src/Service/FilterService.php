<?php

namespace App\Service;

class FilterService
{
    public static function getUniqueRaritiesFromTables($tables){
        $rarities = [];
        foreach($tables as $table){
            if(empty($rarities[$table->getRarity()->getId()])){
                $rarities[$table->getRarity()->getId()] = $table->getRarity();
            }
        }
        // sort rarities by value
        usort($rarities, function($a, $b){
            return $b->getValue() <=> $a->getValue();
        });

        return $rarities;
    }
}