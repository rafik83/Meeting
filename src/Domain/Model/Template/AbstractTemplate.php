<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

abstract class AbstractTemplate
{
    /**
     * @param array  $config
     * @param string $locale
     *
     * @return array
     */
    protected static function createLocale($config, $locale)
    {
        $keys = ['label', 'help', 'placeholder'];

        if (!isset($config['component'])) {
            return array_map(function ($item) use ($locale) {
                return self::createLocale($item, $locale);
            }, $config);
        }

        if ($config['component'] === 'block') {
            foreach ($config['config'] as $key => $column) {
                $config['config'][$key] = self::createLocale($column, $locale);
            }

            return $config;
        }

        if ($config['component'] === 'object') {
            foreach ($config['config'] as $key => $value) {
                if (in_array($key, $keys) || $config['type'] === 'text' && $key === 'content') {
                    $config['config'][$key] = array_merge([$locale => null], $config['config'][$key]);
                }
            }

            return $config;
        }

        return $config;
    }
}
