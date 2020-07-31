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
    public function getPreview(Sheet $sheet, string $locale, ComposedRule $composedRule = null)
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

                // Other remplate objects
                $previewObjects[] = $this->resolveTemplateObject(
                    $templateObject,
                    $locale,
                    $sheet->getEvent()->getLocales()
                );
            } catch (ObjectNotFoundException $exception) {
                continue;
            }
        }

        $this->removeTitleWhenSheetHasNotImage($sheet, $previewObjects);

        return $previewObjects;
    }

    /**
     * @param TemplateObject $templateObject
     * @param string         $locale
     * @param array          $eventLocales
     *
     * @return PreviewView
     */
    private function resolveTemplateObject(TemplateObject $templateObject, string $locale, array $eventLocales)
    {
        $previewView = new PreviewView($templateObject->getKey(), '', $templateObject->getType());

        if ($templateObject instanceof TemplateObject\ContentObjectInterface) {
            if ($templateObject instanceof TemplateObject\EditableText && $templateObject->isTitle()) {
                $previewView->strong = true;
            }

            if ('' === $templateObject->getContentValue() && !empty($templateObject->getTag())) {
                // In EditableText there is only one tag therefore it is not useful to add a comma
                foreach ($templateObject->getTaggedDataViews() as $taggedDataView) {
                    $previewView->content = $this->getTaggedDataViewContent($taggedDataView, $locale);
                }
            } else {
                $previewView->content = $this->getContentValue($templateObject, $eventLocales);
            }

            $previewView->populatedFromTag = $templateObject->getTag();

            if ($templateObject instanceof TemplateObject\Image) {
                $previewView->canDisplayImage = $templateObject->canDisplayImage();
            }

            if ($templateObject instanceof TemplateObject\Video) {
                $previewView->fileMimeType = $templateObject->getMimeType();
            }

            return $previewView;
        }

        if ($templateObject instanceof TemplateObject\Tag) {
            foreach ($templateObject->getTaggedDataViews() as $taggedDataView) {
                $previewView->addTagView(
                    new TagView(
                        $taggedDataView->type,
                        $templateObject->getLabel($locale),
                        $taggedDataView->content
                    )
                );
            }
        }

        return $previewView;
    }

    /**
     * Sheet title is removed when sheet has not image/logo because missing image is already replaced by the sheet title
     *
     * @param Sheet         $sheet
     * @param PreviewView[] $previewObjects
     */
    private function removeTitleWhenSheetHasNotImage(Sheet $sheet, array &$previewObjects)
    {
        $hasEmptyImage = false;

        foreach ($previewObjects as $previewObject) {
            if ($previewObject->isImage() && '' === $previewObject->content) {
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
    }

    /**
     * @param TaggedDataView $taggedDataView
     * @param string         $locale
     *
     * @return string
     */
    private function getTaggedDataViewContent(TaggedDataView $taggedDataView, $locale)
    {
        if (AbstractChild::TEMPLATE_OBJECT_TYPE_BOOLEAN === $taggedDataView->type) {
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
     * @param TemplateObject\ContentObjectInterface $templateObject
     * @param array                                 $eventLocales
     *
     * @return string
     */
    private function getContentValue(TemplateObject\ContentObjectInterface $templateObject, array $eventLocales): string
    {
        if (empty($templateObject->getContentValue())) {
            foreach ($eventLocales as $locale) {
                if (!empty($templateObject->getContentValueLocalize($locale))) {
                    return $templateObject->getContentValueLocalize($locale);
                }
            }
        }

        return $templateObject->getContentValue();
    }
}
