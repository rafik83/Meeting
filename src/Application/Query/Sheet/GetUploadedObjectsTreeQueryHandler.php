<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\TransliteratorAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Sheet\UploadObjectFromFormTemplateView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Domain\Repository\Sheet as SheetRepository;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class GetUploadedObjectsTreeQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TransliteratorAdapterInterface */
    private $transliteratorAdapter;

    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    /** @var SheetRepository\FormDataRepositoryInterface */
    private $sheetFormDataRepository;

    /** @var UserRepository\FormDataRepositoryInterface */
    private $userFormDataRepository;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        TransliteratorAdapterInterface $transliteratorAdapter,
        FormTemplateRepositoryInterface $formTemplateRepository,
        SheetRepository\FormDataRepositoryInterface $sheetFormDataRepository,
        UserRepository\FormDataRepositoryInterface $userFormDataRepository
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->transliteratorAdapter = $transliteratorAdapter;
        $this->formTemplateRepository = $formTemplateRepository;
        $this->sheetFormDataRepository = $sheetFormDataRepository;
        $this->userFormDataRepository = $userFormDataRepository;
    }

    public function handle(GetUploadedObjectsTreeQuery $query): UploadedObjectsTreeView
    {
        $templateDataIndexedByType = [];
        $locale = $query->admin->getLocale();
        $uploadedObjectsTreeView = new UploadedObjectsTreeView();
        $uploadObjectFromFormTemplateViews = $this->getUploadObjectsFromFormTemplateByEvent($query->event, $locale);

        foreach ($query->sheets as $sheet) {
            $templateData = $this->getTemplateDataByType($sheet->getType(), $templateDataIndexedByType, $locale);
            foreach ($templateData->getObjects() as $object) {
                $this->handleObject($object, $sheet, $uploadedObjectsTreeView, $locale);
            }

            /** @var UploadObjectFromFormTemplateView $uploadObjectsFromFormTemplateView */
            foreach ($uploadObjectFromFormTemplateViews as $uploadObjectsFromFormTemplateView) {
                $this->handleFormTemplateObject(
                    $uploadObjectsFromFormTemplateView->formTemplate,
                    $uploadObjectsFromFormTemplateView->templateObject,
                    $sheet,
                    $uploadedObjectsTreeView,
                    $locale
                );
            }
        }

        return $uploadedObjectsTreeView;
    }

    private function handleFormTemplateObject(
        FormTemplate $formTemplate,
        TemplateObject $object,
        Sheet $sheet,
        UploadedObjectsTreeView $uploadedObjectsTreeView,
        string $locale
    ): void {
        if (!$object instanceof UploadObject) {
            return;
        }

        $uploadedObjectNodeView = $this->getUploadedObjectNodeView($object, $uploadedObjectsTreeView, $locale);

        if ($object->hasTag(Tag::SHEET_DATA)) {
            $sheetFormData = $this->sheetFormDataRepository->getBySheetAndFormTemplate($sheet, $formTemplate);

            if ($sheetFormData && isset($sheetFormData->getData()[$object->getKey()]['path'])) {
                $data = $sheetFormData->getData()[$object->getKey()];

                $uploadedObjectNodeView->addUploadedObjectView(
                    new UploadedObjectView(
                        $data['path'],
                        $this->sheetFormTemplateUploadObjectFilename($sheet, $data, $object->getKey()),
                        $object->isCrypted(),
                        $sheet,
                        null,
                        true
                    )
                );
            }
            $uploadedObjectsTreeView->addNode($uploadedObjectNodeView, $object->getKey());
        }

        if ($object->hasTag(Tag::PARTICIPANT_DATA)) {

            foreach ($sheet->getParticipantsArray() as $participant) {
                $userFormData = $this->userFormDataRepository->getByUserAndFormTemplate($participant->getUser(), $formTemplate);

                if ($userFormData && isset($userFormData->getData()[$object->getKey()]['path'])) {
                    $data = $userFormData->getData()[$object->getKey()];

                    $uploadedObjectNodeView->addUploadedObjectView(
                        new UploadedObjectView(
                            $data['path'],
                            $this->participantFormTemplateUploadObjectFilename($sheet, $participant, $data, $object->getKey()),
                            $object->isCrypted(),
                            $sheet,
                            $participant->getUser(),
                            false
                        )
                    );

                    $uploadedObjectsTreeView->addNode($uploadedObjectNodeView, $object->getKey());
                }
            }
        }
    }

    private function handleObject(
        TemplateObject $object,
        Sheet $sheet,
        UploadedObjectsTreeView $uploadedObjectsTreeView,
        string $locale
    ): void {
        if (!$object instanceof UploadObject) {
            return;
        }

        $uploadedObjectNodeView = $this->getUploadedObjectNodeView($object, $uploadedObjectsTreeView, $locale);

        $canAddNode = $this->handleSheetData($object, $sheet, $uploadedObjectNodeView) ||
            $this->handleParticipantData($object, $sheet, $uploadedObjectNodeView);

        if (true === $canAddNode) {
            $uploadedObjectsTreeView->addNode($uploadedObjectNodeView, $object->getKey());
        }
    }

    private function handleSheetData(
        UploadObject $object,
        Sheet $sheet,
        UploadedObjectNodeView $uploadedObjectNodeView
    ): bool {
        if (!$object->hasTag(Tag::SHEET_DATA) || !isset($sheet->getRegistrationData()[$object->getKey()]['path'])) {
            return false;
        }

        $uploadedObjectNodeView->addUploadedObjectView(
            new UploadedObjectView(
                $sheet->getRegistrationData()[$object->getKey()]['path'],
                $this->sheetUploadObjectFilename($sheet, $object->getKey()),
                $object->isCrypted(),
                $sheet,
                null,
                true
            )
        );

        return true;
    }

    private function handleParticipantData(
        UploadObject $object,
        Sheet $sheet,
        UploadedObjectNodeView $uploadedObjectNodeView
    ): bool {
        if (!$object->hasTag(Tag::PARTICIPANT_DATA)) {
            return false;
        }

        $nodeAdded = false;

        foreach ($sheet->getParticipantsArray() as $participant) {
            if (isset($participant->getData()[$object->getKey()]['path'])) {
                $uploadedObjectNodeView->addUploadedObjectView(
                    new UploadedObjectView(
                        $participant->getData()[$object->getKey()]['path'],
                        $this->participantUploadObjectFilename($sheet, $participant, $object->getKey()),
                        $object->isCrypted(),
                        $sheet,
                        $participant->getUser(),
                        false
                    )
                );

                $nodeAdded = true;
            }
        }

        return $nodeAdded;
    }

    private function getUploadedObjectNodeView(
        UploadObject $object,
        UploadedObjectsTreeView $uploadedObjectsTreeView,
        string $locale
    ): UploadedObjectNodeView {
        if (!\array_key_exists($object->getKey(), $uploadedObjectsTreeView->tree)) {
            $folder = $this->transliteratorAdapter->urlize([
                $object->getKey(),
                $object->getLabel($locale),
            ]);

            return new UploadedObjectNodeView($folder);
        }

        return $uploadedObjectsTreeView->tree[$object->getKey()];
    }

    private function getTemplateDataByType(Type $type, array &$templateDataIndexedByType, string $locale): TemplateData
    {
        if (\array_key_exists($type->getId(), $templateDataIndexedByType)) {
            return $templateDataIndexedByType[$type->getId()];
        }

        $templateData = $this->templateDataFactory->createRegistrationFromType($type, $locale);
        $templateDataIndexedByType[$type->getId()] = $templateData;

        return $templateData;
    }

    private function getUploadObjectsFromFormTemplateByEvent(Event $event, string $locale): array
    {
        $uploadObjects = [];
        $formTemplates = $this->formTemplateRepository->findByEvent($event);

        foreach ($formTemplates as $formTemplate) {
            $templateData = $this->templateDataFactory->createFormTemplateFromTemplate($formTemplate, $locale);
            foreach ($templateData->getObjects() as $object) {
                if ($object instanceof UploadObject) {
                    $uploadObjects[] = new UploadObjectFromFormTemplateView($formTemplate, $object);
                }
            }
        }

        return $uploadObjects;
    }

    private function sheetUploadObjectFilename(Sheet $sheet, string $key): string
    {
        $path = $this->transliteratorAdapter->urlize([$sheet->getId(), $sheet->getTitle()]);

        return $path . '.' . $sheet->getRegistrationData()[$key]['extension'];
    }

    private function participantUploadObjectFilename(Sheet $sheet, Participant $participant, string $key): string
    {
        $path = $this->transliteratorAdapter->urlize([
            $sheet->getId(),
            $sheet->getTitle(),
            $participant->getIdAndFullName(),
        ]);

        return $path . '.' . $participant->getData()[$key]['extension'];
    }

    private function participantFormTemplateUploadObjectFilename(Sheet $sheet, Participant $participant, array $data, string $key): string
    {
        $path = $this->transliteratorAdapter->urlize([
            $key,
            $sheet->getId(),
            $sheet->getTitle(),
            $participant->getIdAndFullName(),
        ]);

        return $path . '.' . $data['extension'];
    }

    private function sheetFormTemplateUploadObjectFilename(Sheet $sheet, array $data, string $key): string
    {
        $path = $this->transliteratorAdapter->urlize([$key, $sheet->getId(), $sheet->getTitle()]);

        return $path . '.' . $data['extension'];
    }
}
