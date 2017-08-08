<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Account;


use Proximum\Vimeet\Domain\Exception\Event\NoPreviousEventParticipationException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;

/**
 * PreFill current user registration template data with previous participation template data
 */
class EventParticipationPreFiller
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param TemplateData $templateData
     * @param Event $event
     * @param User $user
     * @param string $locale
     *
     * @return TemplateData
     * @throws NoPreviousEventParticipationException
     */
    public function preFillTemplate(
        TemplateData $templateData,
        Event $event,
        User $user,
        string $locale
    ): TemplateData
    {
        $participants = $this->participantRepository
            ->getParticipantsByUserForEvent($user->getId(), $event);

        if (empty($participants)) {
            throw new NoPreviousEventParticipationException();
        }

        $participant = reset($participants);

        $previousTemplate = $this->templateDataFactory
            ->createRegistrationFromParticipant($participant, $locale);

        $previousTaggedData = [];

        foreach ($previousTemplate->getEditableObjects() as $object) {
            if ($object instanceof ContentObjectInterface) {
                $tags = $object->getTags();

                foreach ($tags as $tag) {
                    $content = $object->getContentValueLocalize($locale);
                    if (empty($previousTaggedData[$tag])) {
                        $previousTaggedData[$tag] = $content;
                    }
                }
            }
        }

        $templateData->setTaggedData($previousTaggedData);

        return $templateData;
    }
}
