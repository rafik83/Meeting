<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\Category;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class CategoryNameResolver
{
    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return string
     */
    public function resolveForPreloadSheets(array &$sheets, string $locale): string
    {
        $categoryTitle = '';

        if (empty($sheets)) {
            return $categoryTitle;
        }

        $typesById       = [];
        $typesByPosition = [];
        $allTypesHavePosition = true;

        foreach ($sheets as $sheet) {
            $type = $sheet->getType();

            if (null !== $type->getPosition()) {
                $typesByPosition[$type->getPosition()] = $type;
            } else {
                $allTypesHavePosition = false;
            }

            $typesById[$type->getId()] = $type;
        }

        $typesToSort = $typesById;

        if ($allTypesHavePosition) {
            $typesToSort = $typesByPosition;
        }

        ksort($typesToSort);

        /** @var Type $type */
        foreach ($typesToSort as $type) {
            /** @var Category[] $categories */
            $categories = $type->getCategories()->toArray();

            if (!empty($categories)) {
                $category = reset($categories);

                return $category->getTitle($locale);
            }
        }

        return $categoryTitle;
    }
}
