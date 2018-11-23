<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Repository\User\FormDataRepositoryInterface as UserFormDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\FormDataRepositoryInterface as SheetFormDataRepositoryInterface;

class FillStepCommandHandler
{
    /** @var UserFormDataRepositoryInterface */
    private $userFormDataRepository;

    /** @var SheetFormDataRepositoryInterface */
    private $sheetFormDataRepository;

    public function __construct(
        UserFormDataRepositoryInterface $userFormDataRepository,
        SheetFormDataRepositoryInterface $sheetFormDataRepository
    ) {
        $this->userFormDataRepository = $userFormDataRepository;
        $this->sheetFormDataRepository = $sheetFormDataRepository;
    }

    public function handle(FillStepCommand $command): void
    {
        $sheetFormData = $this->sheetFormDataRepository->getBySheetAndFormTemplate(
            $command->sheet,
            $command->formTemplate
        );
        $userFormData = $this->userFormDataRepository->getByUserAndFormTemplate(
            $command->participant->getUser(),
            $command->formTemplate
        );

        if (!$sheetFormData instanceof SheetFormData) {
            $sheetFormData = new SheetFormData(
                $command->sheet,
                $command->formTemplate,
                []
            );
        }

        if (!$userFormData instanceof UserFormData) {
            $userFormData = new UserFormData(
                $command->participant->getUser(),
                $command->formTemplate,
                []
            );
        }

        foreach ($command->blockStepView->block->getEditableObjects() as $key => $object) {
            if ($object->hasTag(Tag::SHEET_DATA)) {
                $data = $sheetFormData->getData();

                $data[$key] = $object->getData();
                $sheetFormData->updateData($data);
            }

            if ($object->hasTag(Tag::PARTICIPANT_DATA)) {
                $data = $userFormData->getData();

                $data[$key] = $object->getData();
                $userFormData->updateData($data);
            }
        }

        $this->userFormDataRepository->save($userFormData);
        $this->sheetFormDataRepository->save($sheetFormData);
    }
}
