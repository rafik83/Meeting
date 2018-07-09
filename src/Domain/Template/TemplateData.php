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
        $normalizedConfig = $this->normalize();

        return \is_array($normalizedConfig) ? $normalizedConfig : [];
    }

    public function sanitizedDataWithoutType(array $types = []): void
    {
        if (empty($types)) {
            return;
        }

        foreach ($this->getObjects() as $objectKey => $object) {
            $data = $object->getData();
            $sanitizedData = $data;

            if (\is_array($data)) {
                foreach ($data as $key => $datum) {
                    if (\in_array($key, $types, true)) {
                        unset($sanitizedData[$key]);
                    }
                }
            }

            $object->setData($sanitizedData);
        }
    }
}
