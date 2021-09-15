<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Form\FilledFormStepEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Repository\User\FormDataRepositoryInterface as UserFormDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\FormDataRepositoryInterface as SheetFormDataRepositoryInterface;

class FillStepCommandHandler
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var UserFormDataRepositoryInterface */
    private $userFormDataRepository;

    /** @var SheetFormDataRepositoryInterface */
    private $sheetFormDataRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        UserFormDataRepositoryInterface $userFormDataRepository,
        SheetFormDataRepositoryInterface $sheetFormDataRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->userFormDataRepository = $userFormDataRepository;
        $this->sheetFormDataRepository = $sheetFormDataRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(FillStepCommand $command): void
    {
        $sheetFormData = $this->getSheetFormData($command->sheet, $command->formTemplate);
        $userFormData = $this->getUserFormData($command->participant->getUser(), $command->formTemplate);

        $objects = $command->blockStepView->block->getEditableObjects();

        if ($this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $objects = $command->blockStepView->block->getObjectsEditableByAdmin();
        }

        foreach ($objects as $key => $object) {
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

        $this->delayedEventDispatcher
            ->dispatch(
                Events::FILLED_FORM_STEP,
                new FilledFormStepEvent(
                    $command->formTemplate,
                    $command->participant,
                    $command->blockStepView->block
                )
            )
        ;
    }

    private function getSheetFormData(Sheet $sheet, FormTemplate $formTemplate): SheetFormData
    {
        $sheetFormData = $this->sheetFormDataRepository->getBySheetAndFormTemplate(
            $sheet,
            $formTemplate
        );

        if (!$sheetFormData instanceof SheetFormData) {
            $sheetFormData = new SheetFormData($sheet, $formTemplate, []);
        }

        return $sheetFormData;
    }

    private function getUserFormData(User $user, FormTemplate $formTemplate): UserFormData
    {
        $userFormData = $this->userFormDataRepository->getByUserAndFormTemplate(
            $user,
            $formTemplate
        );

        if (!$userFormData instanceof UserFormData) {
            $userFormData = new UserFormData($user, $formTemplate, []);
        }

        return $userFormData;
    }
}
