<?php

namespace Proximum\Vimeet\Domain\Promotion\Generator;

use Faker\Provider\Base;
use Proximum\Vimeet\Domain\Model\Event;

class FakerCodeGenerator implements CodeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Event $event, ?string $prefix = null): string
    {
        return strtoupper(($prefix ?? '') . Base::lexify('??????'));
    }
}
