<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class GetUploadedObjectsTreeQueryHandler
{
    /** @var TemplateDataFactory $templateDataFactory */
    private $templateDataFactory;

    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    public function handle(GetUploadedObjectsTreeQuery $query): UploadedObjectsTreeView
    {
        $templateDataIndexedByType = [];
        $locale = $query->admin->getLocale();
        $uploadedObjectsTreeView = new UploadedObjectsTreeView();

        foreach ($query->sheets as $sheet) {
            $type = $sheet->getType();

            if (\array_key_exists($type->getId(), $templateDataIndexedByType)) {
                $templateData = $templateDataIndexedByType[$type->getId()];
            } else {
                $templateData = $this->templateDataFactory->createRegistrationFromType($type, $locale);
                $templateDataIndexedByType[$type->getId()] = $templateData;
            }

            foreach ($templateData->getObjects() as $object) {
                if (!$object instanceof UploadObject) {
                    continue;
                }

                $canAddNode = false;

                if (!\array_key_exists($object->getKey(), $uploadedObjectsTreeView->tree)) {
                    $uploadedObjectNodeView = new UploadedObjectNodeView($object->getKey(), $object->getLabel($locale));
                } else {
                    $uploadedObjectNodeView = $uploadedObjectsTreeView->tree[$object->getKey()];
                }

                if (isset($sheet->getRegistrationData()[$object->getKey()]['path'])) {
                    $uploadedObjectNodeView->addUploadedObjectView(
                        new UploadedObjectView(
                            $sheet->getRegistrationData()[$object->getKey()]['path'],
                            $this->sheetUploadObjectFilename($sheet, $object->getKey()),
                            $object->isCrypted()
                        )
                    );

                    $canAddNode = true;
                }

                foreach ($sheet->getParticipantsArray() as $participant) {
                    if (isset($participant->getData()[$object->getKey()]['path'])) {
                        $uploadedObjectNodeView->addUploadedObjectView(
                            new UploadedObjectView(
                                $participant->getData()[$object->getKey()]['path'],
                                $this->participantUploadObjectFilename($sheet, $participant, $object->getKey()),
                                $object->isCrypted()
                            )
                        );

                        $canAddNode = true;
                    }
                }

                if (true === $canAddNode) {
                    $uploadedObjectsTreeView->addNode($uploadedObjectNodeView, $object->getKey());
                }
            }
        }

        return $uploadedObjectsTreeView;
    }

    private function sheetUploadObjectFilename(Sheet $sheet, string $key): string
    {
        $path = Transliterator::urlize($sheet->getId().'-'.$sheet->getTitle());

        return $path.'.'.$sheet->getRegistrationData()[$key]['extension'];
    }

    private function participantUploadObjectFilename(Sheet $sheet, Participant $participant, string $key): string
    {
        $path = Transliterator::urlize($sheet->getId().'-'.$sheet->getTitle().'-'.$participant->getIdAndFullName());

        return $path.'.'.$participant->getData()[$key]['extension'];
    }
}
