<?php

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Validator\CriteriaValidatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Validator\SheetAcceptedCriteriaValidator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Exception\Sheet\NotValidException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SheetValidator
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var CriteriaValidatorInterface[]
     */
    private $validators;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validators      = [
            new SheetAcceptedCriteriaValidator(),
        ];
    }

    /**
     * At least 1 yes and 0 no
     *
     * @param Sheet $sheet
     */
    public function validate(Sheet $sheet)
    {
        try {
            $yesCount = 0;

            foreach ($this->validators as $validator) {
                $result = $validator->isValid($sheet);

                if (CriteriaValidatorInterface::NO === $result) {
                    throw new NotValidException();
                } elseif (CriteriaValidatorInterface::YES === $result) {
                    ++$yesCount;
                }
            }

            if ($yesCount > 0) {
                $sheet->markAsValidated();
                $this->sheetRepository->set($sheet);

                $this->eventDispatcher->dispatch(
                    Events::SHEET_VALIDATED,
                    new SheetValidatedEvent(
                        $sheet,
                        new \DateTime(),
                        '',
                        null
                    )
                );
            }
        } catch (NotValidException $e) {
            return;
        }

        return;
    }
}
