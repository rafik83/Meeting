<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Proximum\Vimeet\Application\Adapter\ProtectedKeyInterface;
use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyGetterInterface;
use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class SheetProtectedKeyGetterAdapter implements SheetProtectedKeyGetterInterface
{
    /** @var ExtraDataRepositoryInterface */
    private $sheetExtraDataRepository;

    /** @var ProtectedKeyInterface */
    private $protectedKey;

    /** @var SheetProtectedKeyPasswordGetterInterface */
    private $sheetProtectedKeyPasswordGetter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $sheetExtraDataRepository,
        ProtectedKeyInterface $protectedKey,
        SheetProtectedKeyPasswordGetterInterface $sheetProtectedKeyPasswordGetter,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->protectedKey = $protectedKey;
        $this->sheetProtectedKeyPasswordGetter = $sheetProtectedKeyPasswordGetter;
        $this->dateTime = $dateTime;
    }

    public function getProtectedKeyBySheet(Sheet $sheet): string
    {
        $extraData = $this->sheetExtraDataRepository->getExtraDataForSheet($sheet, Type::PROTECTED_KEY);

        if ($extraData instanceof Sheet\ExtraData) {
            return $extraData->getValue();
        }

        $password = $this->sheetProtectedKeyPasswordGetter->getProtectedKeyPasswordBySheet($sheet);
        $key = $this->protectedKey->getKeyProtectedByPassword($password);

        $this->sheetExtraDataRepository->add(
            new Sheet\ExtraData($sheet, Type::PROTECTED_KEY, $key, $this->dateTime)
        );

        return $key;
    }
}
