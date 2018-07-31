<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Filters;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;

class GetFiltersByTypeAndLocaleQueryHandler
{
    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    public function __construct(FileSystemAdapterInterface $fileSystemAdapter)
    {
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    public function handle(GetFiltersByTypeAndLocaleQuery $query): array
    {
        $file = sprintf('%s/../../../../Domain/ConditionRules/Rules/%s.json', __DIR__, $query->type);

        if (!$this->fileSystemAdapter->exists($file)) {
            return [];
        }

        $content = json_decode(file_get_contents($file), true);

        return $content[$query->locale] ?? [];
    }
}
