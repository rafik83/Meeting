<?php

namespace Proximum\Vimeet\Domain\Promotion\Generator;

use Proximum\Vimeet\Domain\Model\Event;

interface CodeGeneratorInterface
{
    public function generate(Event $event, ?string $prefix = null): string;
}
