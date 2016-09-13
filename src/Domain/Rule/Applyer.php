<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Domain\Rule\Exception\NoRuleException;
use Proximum\Vimeet\Domain\Template\TemplateData;

class Applyer
{
    /**
     * @var array
     */
    private $tagMapping = [
        Tag::PARTICIPANT_FIRSTNAME => 'firstname',
        Tag::PARTICIPANT_LASTNAME  => 'lastname',
        Tag::PARTICIPANT_AVATAR    => 'avatar',
        Tag::PARTICIPANT_POSITION  => 'position',
    ];

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * @param TemplateData $templateData
     * @param array        $rules
     *
     * @throws Exception\NotRuleException
     */
    public function applyRuleForTemplate(TemplateData &$templateData, array $rules)
    {
        // This try catch should not exist has there should always be a rule in the $rules
        // But to test it, there it is
        try {
            $composedRule = $this->composer->compose($rules);
        } catch (NoRuleException $exception) {
            return ;
        }

        foreach ($templateData->getObjects() as $object) {
            if ($object->getType() === 'tag') {
                if (null !== $tag = $object->getOption('tag')) {
                    if (!in_array($tag, $composedRule->tags)) {
                        $object->setOption('tag', null);
                    }
                }
            } else {
                $tags = $object->getTags();

                foreach($tags as $key => $tag) {
                    if (isset($tag['tag']) && !in_array($tag['tag'], $composedRule->tags)) {
                        $object->removeTag($key);
                    }
                }
            }
        }
    }

    /**
     * @param CardListView $cardListView
     * @param array        $rules
     *
     * @throws Exception\NotRuleException
     */
    public function applyRuleForCardList(CardListView &$cardListView, array $rules)
    {
        // This try catch should not exist has there should always be a rule in the $rules
        // But to test it, there it is
        try {
            $composedRule = $this->composer->compose($rules);
        } catch (NoRuleException $exception) {
            return ;
        }

        foreach ($cardListView->cardViews as $cardView) {
            foreach ($this->tagMapping as $tag => $equivalentVariable) {
                if (!in_array($tag, $composedRule->tags)) {
                    $cardView->$equivalentVariable = '';
                }
            }
        }
    }
}
