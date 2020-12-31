<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;

class ParticipantInfoSetter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param TemplateDataFactory            $templateDataFactory
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->templateDataFactory   = $templateDataFactory;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     * @param string      $phone
     * @param string      $locale
     */
    public function setMobile(Participant $participant, $phone, $locale)
    {
        $templateData = $this->templateDataFactory->createProfileTemplate($participant, $locale);
        $this->replaceValueByTag($participant, $templateData, Tag::PARTICIPANT_MOBILE, $phone);
        $this->participantRepository->set($participant);
    }

    /**
     * @param Participant  $participant
     * @param TemplateData $templateData
     * @param string       $tag
     * @param string       $value
     */
    private function replaceValueByTag(Participant $participant, TemplateData $templateData, $tag, $value)
    {
        $templateObject = $this->getObjectByTag($templateData, $tag);

        if (null !== $templateObject) {
            $this->setValue($participant, $templateData, $templateObject, $value);
        }
    }

    /**
     * @param Participant            $participant
     * @param TemplateData           $templateData
     * @param ContentObjectInterface $templateObject
     * @param string                 $value
     */
    private function setValue(
        Participant $participant,
        TemplateData $templateData,
        ContentObjectInterface $templateObject,
        $value
    ) {
        $templateObject->setContentValue($value);
        $participant->setData($templateData->getData());
    }

    /**
     * @param TemplateData $templateData
     * @param string       $tag
     *
     * @return null|ContentObjectInterface
     */
    private function getObjectByTag(TemplateData $templateData, $tag)
    {
        foreach ($templateData->getProfileObjects() as $templateObject) {
            if ($templateObject->hasTag($tag) && $templateObject instanceof ContentObjectInterface) {
                return $templateObject;
            }
        }

        return null;
    }
}
