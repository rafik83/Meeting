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

    /** @var string */
    private $fallbackLocale;

    public function __construct(FileSystemAdapterInterface $fileSystemAdapter, string $fallbackLocale)
    {
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->fallbackLocale = $fallbackLocale;
    }

    public function handle(GetFiltersByTypeAndLocaleQuery $query): array
    {
        $file = sprintf('%s/../../../../Domain/ConditionRules/Rules/%s.json', __DIR__, $query->type);

        if (!$this->fileSystemAdapter->exists($file)) {
            return [];
        }

        $content = json_decode(file_get_contents($file), true);

        return $content[$query->locale] ?? $content[$this->fallbackLocale] ?? [];
    }
}
