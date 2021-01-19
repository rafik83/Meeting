<?php

namespace Proximum\Vimeet\Application\Adapter;

interface QRCodeGeneratorInterface
{
    public function generateBase64Image(string $text, int $size = 300): string;
}
