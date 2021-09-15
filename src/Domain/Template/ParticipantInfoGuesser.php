<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Helper\NameCleaner;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class ParticipantInfoGuesser
{
    /** @var TaggedInfoGuesser */
    private $taggedInfoGuesser;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
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
     * @param null|string $locale
     *
     * @return string
     */
    public function guessParticipantCompleteName(Participant $participant, ?string $locale = null)
    {
        if (null === $locale) {
            $locale = $participant->getSheet()->getEvent()->getFallback();
        }

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

        return NameCleaner::cleanLastName(
            $this->taggedInfoGuesser->guessFirst(
                $template,
                $participant->getData(),
                Tag::PARTICIPANT_LASTNAME,
                $locale
            )
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

        return NameCleaner::cleanFirstName(
            $this->taggedInfoGuesser->guessFirst(
                $template,
                $participant->getData(),
                Tag::PARTICIPANT_FIRSTNAME,
                $locale
            )
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
     * @return null|string
     */
    public function guessParticipantPhone(Participant $participant, $locale): ?string
    {
        return $this->guessByTag($participant, Tag::PARTICIPANT_PHONE, $locale);
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return null|string
     */
    public function guessParticipantMobile(Participant $participant, string $locale)
    {
        return $this->guessByTag($participant, Tag::PARTICIPANT_MOBILE, $locale);
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array
     */
    public function guessParticipantInfoForMail(Participant $participant, $locale)
    {
        $templateData    = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);
        $mailBuildedInfo = [];

        foreach ($templateData->getAllTaggedDatas() as $tag => $values) {
            $mailBuildedInfo[$tag] = (!empty($values)) ? reset($values) : '';
        }

        return $mailBuildedInfo;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return null|string
     */
    public function guessParticipantPosition(Participant $participant, $locale): ?string
    {
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        return $templateData->getTaggedContentValue(Tag::PARTICIPANT_POSITION);
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return null|string
     */
    public function guessParticipantPositionLabel(Participant $participant, string $locale): ?string
    {
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        foreach ($templateData->getProfileObjects() as $object) {
            if ($object instanceof TemplateObject\ContentObjectInterface
                && $object->hasTag(Tag::PARTICIPANT_POSITION)
            ) {
                if ($object instanceof Nomenclature) {
                    return $object->getContentLabel();
                } else {
                    return $object->getContentValue();
                }
            }
        }

        return null;
    }

    /**
     * @param Participant $participant
     * @param string      $tag
     * @param string      $locale
     *
     * @return null|string
     */
    public function guessByTag(Participant $participant, string $tag, string $locale): ?string
    {
        $template = $participant->getSheet()->getType()->getRegistrationTemplate();

        return $this->taggedInfoGuesser->guessFirst(
            $template,
            $participant->getData(),
            $tag,
            $locale
        );
    }
}
