<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule\Strategy;

abstract class AbstractRecursiveStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $data, array $rules)
    {
        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                if (is_array($rules[$key])) {
                    $data[$key] = $this->apply($value, $rules[$key]);
                } elseif ($rules[$key] === true) {
                    $this->doApply($data, $key);
                }
            }
        }

        return $data;
    }

    /**
     * @param array  $data
     * @param string $key
     */
    abstract function doApply(&$data, $key);
}
