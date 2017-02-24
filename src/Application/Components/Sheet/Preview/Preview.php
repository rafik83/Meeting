<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class Preview
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var Applyer
     */
    private $applyer;

    /**
     * @var CardViewQueryHandler
     */
    private $cardViewQueryHandler;

    /**
     * @param TemplateDataFactory     $templateDataFactory
     * @param CardViewQueryHandler    $cardViewQueryHandler
     * @param Applyer                 $applyer
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        CardViewQueryHandler $cardViewQueryHandler,
        Applyer $applyer
    ) {
        $this->templateDataFactory     = $templateDataFactory;
        $this->applyer                 = $applyer;
        $this->cardViewQueryHandler    = $cardViewQueryHandler;
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
        $templateData      = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $taggedData        = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale)->getAllTaggedDatas();

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
                    && !empty($taggedData[$object->getTag()])
                    && $this->isTagVisible($object->getTag(), $composedRule)
                ) {
                    $previewView->content = reset($taggedData[$object->getTag()]);
                } else {
                    $previewView->content = $object->getContentValue();
                }
            }

            $previewObjects[] = $previewView;
        }

        return $previewObjects;
    }

    /**
     * @param string            $tag
     * @param ComposedRule|null $composedRule
     *
     * @return bool
     */
    private function isTagVisible($tag, ComposedRule $composedRule = null)
    {
        if (null === $composedRule) {
            return true;
        }

        return in_array($tag, $composedRule->tags);
    }
}
