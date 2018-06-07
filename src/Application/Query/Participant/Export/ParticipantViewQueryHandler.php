<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class ParticipantViewQueryHandler
{
    /** @var null|\IntlDateFormatter */
    private $timeFormatter;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        TemplateDataFactory $templateDataFactory,
        TranslatorInterface $translator
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->translator = $translator;
    }

    public function handle(ParticipantViewQuery $query): ParticipantView
    {
        if (null === $this->timeFormatter) {
            $this->timeFormatter = \IntlDateFormatter::create(
                $query->locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE,
                $query->event->getTimeZone()
            );
        }

        $registrationData = $this->getRegistrationData($query->participant, $query->locale);

        $view = new ParticipantView(
            $query->participant->getSheet()->getId(),
            $query->participant->getSheet()->getType()->getTitle($query->locale),
            $query->participant->getSheet()->getTitle(),
            $query->participant->getSheet()->isEnabled(),
            $query->participant->getUser()->getId(),
            $query->participant->getId(),
            $query->participant->getEmail(),
            $this->timeFormatter->format($query->participant->getSheet()->getCreatedAt()),
            $this->happeningParticipationRepository->hasParticipationForUserAndEvent($query->participant->getUser(), $query->event),
            $registrationData
        );

        return $view;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array
     */
    private function getRegistrationData(Participant $participant, string $locale): array
    {
        $registrationData = [];
        $template         = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $fieldContent = $registrationObject->getExportableContent();

                if ($registrationObject instanceof Gender) {
                    $fieldContent = $this->translator->trans(sprintf('gender.%s', $fieldContent), [], null, $locale);
                }

                if ($registrationObject instanceof BooleanObject) {
                    $fieldContent = $this->translator->trans(sprintf('boolean.%s', $fieldContent), [], null, $locale);
                }

                $registrationData[$registrationObject->getKey()] = $fieldContent;
            }
        }

        return $registrationData;
    }
}
