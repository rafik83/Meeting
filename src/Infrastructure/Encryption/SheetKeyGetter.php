<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyGetterInterface;
use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetKeyGetter
{
    /** @var SheetProtectedKeyGetterInterface */
    private $sheetProtectedKeyGetter;

    /** @var SheetProtectedKeyPasswordGetterInterface */
    private $sheetProtectedKeyPasswordGetter;

    public function __construct(
        SheetProtectedKeyGetterInterface $sheetProtectedKeyGetter,
        SheetProtectedKeyPasswordGetterInterface $sheetProtectedKeyPasswordGetter
    ) {
        $this->sheetProtectedKeyGetter = $sheetProtectedKeyGetter;
        $this->sheetProtectedKeyPasswordGetter = $sheetProtectedKeyPasswordGetter;
    }

    public function getKeyBySheet(Sheet $sheet): Key
    {
        $protectedKey = $this->sheetProtectedKeyGetter->getProtectedKeyBySheet($sheet);
        $protectedKeyPassword = $this
            ->sheetProtectedKeyPasswordGetter
            ->getProtectedKeyPasswordBySheet($sheet)
        ;

        $keyProtectedByPassword = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);

        return $keyProtectedByPassword->unlockKey($protectedKeyPassword);
    }
}
