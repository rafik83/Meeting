<?php

namespace Proximum\Vimeet\Domain\Account;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;

/**
 * PreFill current user registration template data with previous participation template data
 */
class EventParticipationPreFiller
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param TemplateData $templateData
     * @param Participant  $participant
     * @param string       $locale
     *
     * @return TemplateData
     */
    public function preFillTemplate(
        TemplateData $templateData,
        Participant $participant,
        string $locale
    ): TemplateData {
        $previousTemplate = $this->templateDataFactory
            ->createRegistrationFromParticipant($participant, $locale);

        $previousTaggedData = [];

        foreach ($previousTemplate->getEditableObjects() as $object) {
            if ($object instanceof ContentObjectInterface) {
                $this->preFillByTags($object, $locale, $previousTaggedData);
                $this->preFillByKey($object, $templateData);
            }
        }

        // pre fill old tagged data in new template
        $templateData->setTaggedDataIfEmpty($previousTaggedData);

        return $templateData;
    }

    /**
     * @param ContentObjectInterface|TemplateObject $templateObject
     * @param string                                $locale
     * @param array                                 $previousTaggedData
     */
    private function preFillByTags(
        ContentObjectInterface $templateObject,
        string $locale,
        array &$previousTaggedData
    ) {
        $tags = $templateObject->getTags();

        foreach ($tags as $tag) {
            if (Tag::SHEET_DATA === $tag || Tag::PARTICIPANT_DATA === $tag) {
                continue;
            }

            $content = $templateObject->getContentValueLocalize($locale);
            if (empty($previousTaggedData[$tag]) && !empty($content)) {
                $previousTaggedData[$tag] = $content;
            }
        }
    }

    /**
     * @param TemplateObject $previousTemplateObject
     * @param TemplateData   $templateData
     */
    private function preFillByKey(TemplateObject $previousTemplateObject, TemplateData &$templateData)
    {
        try {
            $templateObject = $templateData->getObject($previousTemplateObject->getKey());
        } catch (ObjectNotFoundException $exception) {
            return;
        }

        if (!empty($previousTemplateObject->getData())) {
            $templateObject->setData($previousTemplateObject->getData());
        }
    }
}
