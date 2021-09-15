<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Endroid\QrCode\QrCode;
use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;

class QRCodeGeneratorAdapter implements QRCodeGeneratorInterface
{
    public function generateBase64Image(string $text, int $size = 300): string
    {
        $qrCode = new QrCode($text);
        $qrCode->setText($text);

        return $qrCode->writeDataUri();
    }
}
