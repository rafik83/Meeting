<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;

class ExportQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param ExportQuery $exportQuery
     *
     * @return ParticipantListView
     */
    public function handle(ExportQuery $exportQuery): ParticipantListView
    {
        $participants = $this->participantRepository->findByIds($exportQuery->participantIds);

        $prepareColumns = [];
        $typesHandled = [];
        $participantViews= [];

        foreach ($participants as $participant) {
            if (!isset($typesHandled[$participant->getSheet()->getType()->getId()])) {
                $this->prepareRegistrationColumns($prepareColumns, $participant->getSheet()->getType(), $exportQuery->locale);

                $typesHandled[$participant->getSheet()->getType()->getId()] = true;
            }

            $participantViews[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery($exportQuery->event, $participant, $exportQuery->locale)
            );
        }

        return new ParticipantListView(
            $exportQuery->locale,
            $participantViews,
            $prepareColumns
        );
    }

    private function prepareRegistrationColumns(array &$prepareColumns, Type $type, string $locale): void
    {
        $template = $this->templateDataFactory->createRegistrationFromType($type, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();

                if (!isset($prepareColumns[$key])) {
                    $prepareColumns[$key] =  $registrationObject->getExportableFieldname(
                        $locale,
                        $locale
                    );
                }
            }
        }
    }
}
