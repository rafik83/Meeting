<?php

namespace Proximum\Vimeet\Domain\Model;

interface MessageInterface
{
    public function getSubject(string $locale): string;

    public function getContent(string $locale): string;
}
