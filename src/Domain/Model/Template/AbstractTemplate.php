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
     * @return string
     */
    abstract public function getFallback();

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
            return self::createComponents($config, $locale);
        }

        if ($config['component'] === 'block') {
            return self::createBlock($config, $locale);
        }

        if ($config['component'] === 'object') {
            return self::createObject($config, $locale, $keys);
        }

        return $config;
    }

    /**
     * @param $config
     * @param $locale
     *
     * @return array
     */
    protected static function createComponents($config, $locale)
    {
        return array_map(
            function ($item) use ($locale) {
                return self::createLocale($item, $locale);
            }, $config
        );
    }

    /**
     * @param $config
     * @param $locale
     *
     * @return mixed
     */
    protected static function createBlock($config, $locale)
    {
        foreach ($config['children'] as $key => $column) {
            $config['children'][$key] = self::createLocale($column, $locale);
        }

        return $config;
    }

    /**
     * @param $config
     * @param $locale
     * @param $keys
     *
     * @return mixed
     */
    protected static function createObject($config, $locale, $keys)
    {
        foreach ($config['config'] as $key => $value) {
            if (in_array($key, $keys) || $config['type'] === 'text' && $key === 'content') {
                $config['config'][$key] = array_merge([$locale => null], $config['config'][$key]);
            }
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
