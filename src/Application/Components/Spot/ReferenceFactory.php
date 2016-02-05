<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot;

class ReferenceFactory
{
    /**
     * @param array $recipes
     *
     * @return array
     */
    public function createFromRecipes(array $recipes)
    {
        return array_reduce(array_map([$this, 'createFromRecipe'], $recipes), function (array $carry, array $recipes) {
            return array_merge($carry, $recipes);
        }, []);
    }

    /**
     * @param Recipe $recipe
     *
     * @return array
     */
    public function createFromRecipe(Recipe $recipe)
    {
        if ($recipe->begin) {
            if ($recipe->end) {
                return array_map(function ($number) use ($recipe) {
                    return $recipe->prefix.str_pad($number, floor($recipe->end / 10) + 1, 0, STR_PAD_LEFT);
                }, range($recipe->begin, $recipe->end, 1));
            }

            return [$recipe->prefix.$recipe->begin];
        }

        return [$recipe->prefix];
    }
}
