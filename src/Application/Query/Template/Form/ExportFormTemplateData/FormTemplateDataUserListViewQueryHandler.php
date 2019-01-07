<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserListView;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class FormTemplateDataUserListViewQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;
    /** @var FormTemplateDataForUserHandler */
    private $formTemplateDataForUserHandler;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        FormTemplateDataForUserHandler $formTemplateDataForUserHandler
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->formTemplateDataForUserHandler = $formTemplateDataForUserHandler;
    }

    public function handle(FormTemplateDataUserListViewQuery $query): UserListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);
        $userViews = [];

        foreach ($query->users as $user) {
            $userView = $this->formTemplateDataForUserHandler->handle(new FormTemplateDataForUser($query->event, $user, $query->formTemplate, $locale));

            if ($userView instanceof UserDataView) {
                $userViews[] = $userView;
            }
        }

        $templateData = $this->templateDataFactory->createFormTemplateFromTemplate($query->formTemplate, $locale);
        $objectLabels = [];

        foreach ($templateData->getExportableObjects() as $object) {
            $objectLabels[$object->getKey()] = $object->getExportableFieldname($query->locale, $query->locale);
        }

        return new UserListView(
            $query->locale,
            $userViews,
            $objectLabels
        );
    }
}
