<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Validator\CriteriaValidatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Validator\SheetAcceptedCriteriaValidator;
use Proximum\Vimeet\Application\Exception\Sheet\NotValidException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

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
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
        $this->validators      = [
            new SheetAcceptedCriteriaValidator()
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

                if ($result === CriteriaValidatorInterface::NO) {
                    throw new NotValidException();
                } elseif ($result === CriteriaValidatorInterface::YES) {
                    $yesCount++;
                }
            }

            if ($yesCount > 0) {
                $sheet->markAsValidated();
                $this->sheetRepository->set($sheet);
            }
        } catch (NotValidException $e) {
            return;
        }

        return;
    }
}
