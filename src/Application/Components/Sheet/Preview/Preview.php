<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateType;
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
        $this->taggedDataFactory       = $taggedDataFactory;
        $this->applyer                 = $applyer;
        $this->cardViewQueryHandler    = $cardViewQueryHandler;
        $this->translator              = $translator;
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
        $templateData      = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale, [$composedRule->rule]);

        foreach ($previewObjectKeys as $key) {
            $object = $templateData->getObject($key);

            if (empty($cardViews) && $object instanceof TemplateObject\Participant) {
                $participants       = $sheet->getParticipants()->toArray();
                $numberParticipants = $object->getNumberOfParticipantShown();

                // Create card view for each participant limited by the number of participant shown
                for ($index = 0; $index < $numberParticipants && isset($participants[$index]); $index++) {
                    $cardView = $this->cardViewQueryHandler->handle(new CardViewQuery($participants[$index], $locale));

                    if (null !== $composedRule && null !== $composedRule->rule) {
                        $this->applyer->applyRuleForParticipantCard($cardView, [$composedRule->rule]);
                    }

                    $cardViews[] = $cardView;
                }
            }

            $previewView = new PreviewView($object->getKey(), '', $object->getType(), $cardViews);

            if ($object instanceof TemplateObject\ContentObjectInterface) {
                if ($object instanceof TemplateObject\EditableText && $object->isTitle()) {
                    $previewView->strong = true;
                }

                if ($object->getContentValue() === ''
                    && $object->getTag() !== null
                ) {
                    foreach ($object->getTaggedDataViews() as $taggedDataView) {
                        $previewView->content = $this->getTaggedDataViewContent($taggedDataView, $locale);
                    }
                } else {
                    $previewView->content = $object->getContentValue();
                }
            }

            $previewObjects[] = $previewView;
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
        if ($taggedDataView->type === TemplateType::TEMPLATE_OBJECT_TYPE_BOOLEAN) {
            return $this->translator->trans(sprintf('gender.%s'), 'messages', $locale);
        }

        return $taggedDataView->content;
    }
}
