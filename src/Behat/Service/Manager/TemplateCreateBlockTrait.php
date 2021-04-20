<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

trait TemplateCreateBlockTrait
{
    private function createBlock(array $children, string $type = '8-4'): array
    {
        return [
            'component' => 'block',
            'type' => $type,
            'config' => [],
            'children' => [$children]
        ];
    }
}
