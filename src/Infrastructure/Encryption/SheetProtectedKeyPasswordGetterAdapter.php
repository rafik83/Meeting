<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetProtectedKeyPasswordGetterAdapter implements SheetProtectedKeyPasswordGetterInterface
{
    /** @var string */
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function getProtectedKeyPasswordBySheet(Sheet $sheet): string
    {
        return hash('sha256', sprintf('_%d_%d_%s_', $sheet->getEvent()->getId(), $sheet->getId(), $this->secretKey));
    }
}
