<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetAlreadyRegisteredException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoExtraDataType;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class VianeoRegisterSheetHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param \DateTimeInterface           $dateTime
     */
    public function __construct(ExtraDataRepositoryInterface $extraDataRepository, \DateTimeInterface $dateTime)
    {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param VianeoRegisterSheet $vianeoRegisterSheet
     *
     * @throws VianeoSheetAlreadyRegisteredException
     */
    public function handle(VianeoRegisterSheet $vianeoRegisterSheet)
    {
        if (true === $this->extraDataRepository->hasExtraDataForSheet(
            $vianeoRegisterSheet->sheet,
            VianeoExtraDataType::VIANEO_SHEET_REGISTERED
        )) {
            throw new VianeoSheetAlreadyRegisteredException();
        }

        $this->extraDataRepository->add(
            new ExtraData(
                $vianeoRegisterSheet->sheet,
                VianeoExtraDataType::VIANEO_SHEET_REGISTERED,
                null,
                $this->dateTime
            )
        );
    }
}
