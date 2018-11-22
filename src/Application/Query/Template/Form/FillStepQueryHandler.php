<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Repository\Sheet as SheetRepository;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class FillStepQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SheetRepository\FormDataRepositoryInterface */
    private $sheetFormDataRepository;

    /** @var UserRepository\FormDataRepositoryInterface */
    private $userFormDataRepository;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetRepository\FormDataRepositoryInterface $sheetFormDataRepository,
        UserRepository\FormDataRepositoryInterface $userFormDataRepository
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->sheetFormDataRepository = $sheetFormDataRepository;
        $this->userFormDataRepository = $userFormDataRepository;
    }

    /**
     * @param FillStepQuery $query
     *
     * @return BlockStepView
     *
     * @throws BlockForGivenStepNotFoundException
     */
    public function handle(FillStepQuery $query): BlockStepView
    {
        $sheetFormData = $this->sheetFormDataRepository->getBySheetAndFormTemplate($query->sheet, $query->formTemplate);
        $userFormData = $this->userFormDataRepository->getByUserAndFormTemplate($query->participant->getUser(), $query->formTemplate);
        $data = [];

        if ($sheetFormData instanceof SheetFormData) {
            $data = $sheetFormData->getData();
        }

        if ($userFormData instanceof UserFormData) {
            $data = array_merge($data, $userFormData->getData());
        }

        $templateData = $this->templateDataFactory->createFormTemplateWithData(
            $query->formTemplate,
            $data,
            $query->locale
        );

        $block = $templateData->getBlock($query->step);

        if (!$block instanceof Block) {
            throw new BlockForGivenStepNotFoundException($query->step);
        }

        return new BlockStepView($block, $query->step, $templateData->getBlocksCount());
    }
}
