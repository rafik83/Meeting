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
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsPositionResolver;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsResolver;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Application\View\Sheet\Preview\TagView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;

class Preview
{
    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ParticipantsResolver */
    private $participantsResolver;

    /** @var ParticipantsPositionResolver */
    private $participantsPositionResolver;

    /**
     * @param TaggedDataFactory            $taggedDataFactory
     * @param TranslatorInterface          $translator
     * @param ParticipantsResolver         $participantsResolver
     * @param ParticipantsPositionResolver $participantsPositionResolver
     */
    public function __construct(
        TaggedDataFactory $taggedDataFactory,
        TranslatorInterface $translator,
        ParticipantsResolver $participantsResolver,
        ParticipantsPositionResolver $participantsPositionResolver
    ) {
        $this->taggedDataFactory            = $taggedDataFactory;
        $this->translator                   = $translator;
        $this->participantsResolver         = $participantsResolver;
        $this->participantsPositionResolver = $participantsPositionResolver;
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
        $previewObjects    = [];
        $previewObjectKeys = $sheet->getTypeSheetTemplate()->getPreview();
        $rules             = null !== $composedRule ? [$composedRule->rule] : [];
        $templateData      = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale, $rules);

        foreach ($previewObjectKeys as $key) {
            try {
                // Manage custom preview data
                if (CustomPreviewData::PARTICIPANTS_POSITION === $key) {
                    $customPreviewView = $this->participantsPositionResolver->handle($sheet, $locale);

                    if ($customPreviewView instanceof PreviewView) {
                        $previewObjects[] = $customPreviewView;
                    }

                    continue;
                }

                // then manage template object data
                $templateObject = $templateData->getObject($key);

                // Participant object
                if ($templateObject instanceof TemplateObject\Participant) {
                    $previewObjects[] = $this->participantsResolver->handle($sheet, $locale, $templateObject, $rules);

                    continue;
                }

                $previewView = new PreviewView($templateObject->getKey(), '', $templateObject->getType());

                if ($templateObject instanceof TemplateObject\ContentObjectInterface) {
                    if ($templateObject instanceof TemplateObject\EditableText && $templateObject->isTitle()) {
                        $previewView->strong = true;
                    }


                    if ($templateObject->getContentValue() === '' && $templateObject->getTag() !== null) {
                        // In EditableText there is only one tag therefore it is not useful to add a comma
                        foreach ($templateObject->getTaggedDataViews() as $taggedDataView) {
                            $previewView->content = $this->getTaggedDataViewContent($taggedDataView, $locale);
                        }
                    } else {
                        $previewView->content = $templateObject->getContentValue();
                    }

                    $previewView->populatedFromTag = $templateObject->getTag();
                } elseif ($templateObject instanceof TemplateObject\Tag) {
                    foreach ($templateObject->getTaggedDataViews() as $taggedDataView) {
                        $previewView->addTagView(
                            new TagView($taggedDataView->type, $templateObject->getLabel($locale), $taggedDataView->content)
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
}
