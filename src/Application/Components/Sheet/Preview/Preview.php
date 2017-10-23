<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Application\View\Sheet\Preview\TagView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;

class Preview
{
    /** @var Applyer */
    private $applyer;

    /** @var CardViewQueryHandler */
    private $cardViewQueryHandler;

    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TaggedDataFactory    $taggedDataFactory
     * @param CardViewQueryHandler $cardViewQueryHandler
     * @param Applyer              $applyer
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        TaggedDataFactory $taggedDataFactory,
        CardViewQueryHandler $cardViewQueryHandler,
        Applyer $applyer,
        TranslatorInterface $translator
    ) {
        $this->taggedDataFactory    = $taggedDataFactory;
        $this->applyer              = $applyer;
        $this->cardViewQueryHandler = $cardViewQueryHandler;
        $this->translator           = $translator;
    }

    /**
     * @param Sheet             $sheet
     * @param string            $locale
     * @param ComposedRule|null $composedRule
     *
     * @return PreviewView[]
     */
    public function getPreview(Sheet $sheet, $locale, ComposedRule $composedRule = null)
    {
        $cardViews         = [];
        $previewObjects    = [];
        $previewObjectKeys = $sheet->getTypeSheetTemplate()->getPreview();
        $rules             = null !== $composedRule ? [$composedRule->rule] : [];
        $templateData      = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale, $rules);


        foreach ($previewObjectKeys as $key) {
            try {
                $customPreviewView = $this->getCustomPreviewView($sheet, $key);

                if ($customPreviewView instanceof PreviewView) {
                    $previewObjects[] = $customPreviewView;

                    continue;
                }

                $object = $templateData->getObject($key);

                if (empty($cardViews) && $object instanceof TemplateObject\Participant) {
                    $participants       = $sheet->getParticipants()->toArray();
                    $numberParticipants = $object->getNumberOfParticipantShown();

                    // Create card view for each participant limited by the number of participant shown
                    for ($index = 0; $index < $numberParticipants && isset($participants[$index]); $index++) {
                        $cardView = $this->cardViewQueryHandler->handle(
                            new CardViewQuery($participants[$index], $locale)
                        );

                        if (null !== $composedRule && null !== $composedRule->rule) {
                            $this->applyer->applyRuleForParticipantCard($cardView, $rules);
                        }

                        $cardViews[] = $cardView;
                    }
                }

                $previewView = new PreviewView($object->getKey(), '', $object->getType(), $cardViews);

                if ($object instanceof TemplateObject\ContentObjectInterface) {
                    if ($object instanceof TemplateObject\EditableText && $object->isTitle()) {
                        $previewView->strong = true;
                    }


                    if ($object->getContentValue() === '' && $object->getTag() !== null) {
                        // In EditableText there is only one tag therefore it is not useful to add a comma
                        foreach ($object->getTaggedDataViews() as $taggedDataView) {
                            $previewView->content = $this->getTaggedDataViewContent($taggedDataView, $locale);
                        }
                    } else {
                        $previewView->content = $object->getContentValue();
                    }

                    $previewView->populatedFromTag = $object->getTag();
                } elseif ($object instanceof TemplateObject\Tag) {
                    foreach ($object->getTaggedDataViews() as $taggedDataView) {
                        $previewView->addTagView(
                            new TagView($taggedDataView->type, $object->getLabel($locale), $taggedDataView->content)
                        );
                    }
                }

                $previewObjects[] = $previewView;
            } catch (ObjectNotFoundException $exception) {
                continue;
            }
        }

        $hasEmptyImage = false;

        foreach ($previewObjects as $previewObject) {
            if ($previewObject->isImage() && $previewObject->content === '') {
                $hasEmptyImage = true;
                break;
            }
        }

        if ($hasEmptyImage) {
            foreach ($previewObjects as $previewObjectKey => $previewObjectValue) {
                if ($previewObjectValue->isPopulatedFromTagSheetOrganization()
                    && $previewObjectValue->content === $sheet->getTitle()
                ) {
                    unset($previewObjects[$previewObjectKey]);
                }
            }
        }

        return $previewObjects;
    }

    /**
     * @param TaggedDataView $taggedDataView
     * @param string         $locale
     *
     * @return string
     */
    private function getTaggedDataViewContent(TaggedDataView $taggedDataView, $locale)
    {
        if ($taggedDataView->type === AbstractChild::TEMPLATE_OBJECT_TYPE_BOOLEAN) {
            return $this->translator->trans(
                sprintf('sheet.object.boolean.%s', $taggedDataView->content ? 'true' : 'false'),
                [],
                'messages',
                $locale
            );
        }

        return $taggedDataView->content;
    }

    /**
     * @param Sheet  $sheet
     * @param string $key
     *
     * @return null|PreviewView
     */
    private function getCustomPreviewView(Sheet $sheet, string $key): ?PreviewView
    {
        if (null === CustomPreviewData::getCustomPreviewDataViewByName($key)) {
            return null;
        }

        if (CustomPreviewData::PARTICIPANTS_POSITION === $key) {
            return $this->resolveParticipantPositions($sheet);
        }

        throw new \LogicException('Missing preview resolver for key ' . $key);
    }

    /**
     * @param Sheet  $sheet
     *
     * @return PreviewView
     */
    private function resolveParticipantPositions(Sheet $sheet): PreviewView
    {
        return new PreviewView(
            CustomPreviewData::PARTICIPANTS_POSITION,
            'position names',
            CustomPreviewData::PARTICIPANTS_POSITION,
            [],
            $sheet->countParticipants() > 1 // show link if more than one participant
        );
    }
}
