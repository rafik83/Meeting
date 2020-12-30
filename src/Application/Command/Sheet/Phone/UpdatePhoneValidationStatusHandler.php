<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Phone;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationCalculator;

class UpdatePhoneValidationStatusHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ValidationCalculator */
    private $validationCalculator;

    /**
     * @param TypeRepositoryInterface  $typeRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param ValidationCalculator     $validationCalculator
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository,
        ValidationCalculator $validationCalculator
    ) {
        $this->typeRepository = $typeRepository;
        $this->sheetRepository = $sheetRepository;
        $this->validationCalculator = $validationCalculator;
    }

    /**
     * @param UpdatePhoneValidationStatus $command
     */
    public function handle(UpdatePhoneValidationStatus $command)
    {
        $types = $this->typeRepository->getTypesByEvent($command->event);

        $this->validationCalculator->preloadTypeThatAllowPhones($command->event, $types);

        $sheets = $this->sheetRepository->getByTypes($types);

        foreach ($sheets as $sheet) {
            if (!$sheet->isEnabled()) {
                continue;
            }

            $status = $this->validationCalculator->getValidationStatusForSheet($sheet);

            $sheet->setPhoneValidationStatus($status);
            $this->sheetRepository->set($sheet);
        }
    }
}
