<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\TransliteratorAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class GetUploadedObjectsTreeQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TransliteratorAdapterInterface */
    private $transliteratorAdapter;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        TransliteratorAdapterInterface $transliteratorAdapter
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->transliteratorAdapter = $transliteratorAdapter;
    }

    public function handle(GetUploadedObjectsTreeQuery $query): UploadedObjectsTreeView
    {
        $templateDataIndexedByType = [];
        $locale = $query->admin->getLocale();
        $uploadedObjectsTreeView = new UploadedObjectsTreeView();

        foreach ($query->sheets as $sheet) {
            $templateData = $this->getTemplateDataByType($sheet->getType(), $templateDataIndexedByType, $locale);

            foreach ($templateData->getObjects() as $object) {
                $this->handleObject($object, $sheet, $uploadedObjectsTreeView, $locale);
            }
        }

        return $uploadedObjectsTreeView;
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
                $sheet
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
                        $participant->getUser()
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
}
