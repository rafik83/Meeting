<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Exception\Type\TypeUsedBySheetException;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RemoveHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param TypeRepositoryInterface  $typeRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param RuleRepositoryInterface  $ruleRepository
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->typeRepository  = $typeRepository;
        $this->sheetRepository = $sheetRepository;
        $this->ruleRepository  = $ruleRepository;
    }

    /**
     * @param Remove $remove
     *
     * @throws TypeUsedBySheetException
     */
    public function handle(Remove $remove)
    {
        if ($this->sheetRepository->isThereAtLeastOneByType($remove->type)) {
            throw new TypeUsedBySheetException();
        }

        // remove associated rule for type
        $rules = $this->ruleRepository->getByType($remove->type);

        if (!empty($rules)) {
            foreach($rules as $rule) {
                $this->ruleRepository->remove($rule);
            }
        }

        $this->typeRepository->remove($remove->type);
    }
}
