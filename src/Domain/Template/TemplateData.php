<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

class TemplateData extends Block
{
    public function getConfig(): array
    {
        return $this->normalize();
    }

    public function sanitizedDataWithoutType(string $type): array
    {
        $data = $this->getData();

        foreach ($data as $key => $datum) {
            if (isset($datum['product'])) {
                $datum['product'] = null;
            }

            $data[$key] = array_filter($datum, function ($element) use ($type) {
                return $type !== $element;
            }, ARRAY_FILTER_USE_KEY);
        }

        return $data;
    }
}
