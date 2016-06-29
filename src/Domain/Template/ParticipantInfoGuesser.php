<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * ParticipantInfoGuesser constructor.
     *
     * @param TaggedInfoGuesser   $taggedInfoGuesser
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser, TemplateDataFactory $templateDataFactory)
    {
        $this->taggedInfoGuesser   = $taggedInfoGuesser;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantCompleteName(Participant $participant, $locale)
    {
        $infos = $this->guessParticipantInfos($participant, $locale);

        return (isset($infos[Tag::PARTICIPANT_FIRSTNAME]) ? $infos[Tag::PARTICIPANT_FIRSTNAME] : '')
            . ' '
            . (isset($infos[Tag::PARTICIPANT_LASTNAME]) ? $infos[Tag::PARTICIPANT_LASTNAME] : '');
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantLastName(Participant $participant, $locale)
    {
        $template = $participant->getSheet()->getType()->getRegistrationTemplate();

        return $this->taggedInfoGuesser->guessFirst(
            $template,
            $participant->getData(),
            Tag::PARTICIPANT_LASTNAME,
            $locale
        );
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantFirstName(Participant $participant, $locale)
    {
        $template = $participant->getSheet()->getType()->getRegistrationTemplate();

        return $this->taggedInfoGuesser->guessFirst(
            $template,
            $participant->getData(),
            Tag::PARTICIPANT_FIRSTNAME,
            $locale
        );
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array
     */
    public function guessParticipantInfos(Participant $participant, $locale)
    {
        $tags         = Tag::getParticipantTags();
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);
        $infos        = [];

        foreach ($tags as $tag) {
            $infos[$tag] = $this->taggedInfoGuesser->guessFirstFromTemplateData(
                $templateData,
                $tag
            );
        }

        return $infos;
    }

    /**
     * @param TemplateData $templateData
     *
     * @return array
     */
    public function guessParticipantInfosWithTemplateData(TemplateData $templateData)
    {
        $tags  = Tag::getParticipantTags();
        $infos = [];

        foreach ($tags as $tag) {
            $infos[$tag] = $this->taggedInfoGuesser->guessFirstFromTemplateData(
                $templateData,
                $tag
            );
        }

        return $infos;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantPhone(Participant $participant, $locale)
    {
        $template = $participant->getSheet()->getType()->getRegistrationTemplate();

        return $this->taggedInfoGuesser->guessFirst(
            $template,
            $participant->getData(),
            Tag::PARTICIPANT_PHONE,
            $locale
        );
    }

    /**
     * @param Participant $participant
     * @param             $locale
     *
     * @return array
     */
    public function guessParticipantInfoForMail(Participant $participant, $locale)
    {
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        $mailBuildedInfo = [];
        foreach ($templateData->getAllTaggedDatas() as $tag => $values) {
            if ($tag == Tag::SHEET_ORGANIZATION) {
                $mailBuildedInfo['organisation'] = (!empty($values)) ? reset($values) : '';
            }

            if ($tag == Tag::PARTICIPANT_POSITION) {
                $mailBuildedInfo['position'] = (!empty($values)) ? reset($values) : '';
            }
        }

        return $mailBuildedInfo;
    }
}
