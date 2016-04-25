<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractTemplate
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var array
     */
    protected $locales = [];

    /**
     * @var string
     */
    protected $fallback;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Type[]
     */
    protected $types;

    /**
     * @var DateTimeInterface
     */
    protected $createdAt;

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
            foreach ($config['children'] as $key => $column) {
                $config['children'][$key] = self::createLocale($column, $locale);
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

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }
}
