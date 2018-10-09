<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class QueryKeyTransformer
{
    public static function getQueryKey(Field $field): string
    {
        return TypesMapping::SEARCH_MAPPING[$field->getField()]['path'] ?? '';
    }
}
