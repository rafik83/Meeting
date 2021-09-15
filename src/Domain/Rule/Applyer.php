<?php

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Application\View\Participant\CardView;
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
            return;
        }

        foreach ($templateData->getObjects() as $object) {
            if ('tag' === $object->getType()) {
                if (null !== $tag = $object->getOption('tag')) {
                    if (!in_array($tag, $composedRule->tags, true)) {
                        $object->setOption('tag', null);
                    }
                }
            } else {
                $tags = $object->getTags();

                foreach ($tags as $key => $tag) {
                    if (isset($tag['tag']) && !in_array($tag['tag'], $composedRule->tags, true)) {
                        $object->removeTag($key);
                    }
                }
            }
        }
    }

    /**
     * @param TemplateData $templateData
     * @param array        $rules
     */
    public function applyRuleForRegistrationTemplate(TemplateData &$templateData, array $rules)
    {
        try {
            $composedRule = $this->composer->compose($rules);
        } catch (NoRuleException $exception) {
            return;
        }

        foreach ($templateData->getObjects() as $object) {
            $tagFound = false;
            foreach ($composedRule->tags as $tag) {
                if (in_array($tag, $object->getTags(), true)) {
                    $tagFound = true;

                    break;
                }
            }

            if (!$tagFound) {
                $object->setData([]);
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
        try {
            $composedRule = $this->composer->compose($rules);
        } catch (NoRuleException $exception) {
            // If no rule, create a composedRule without tags to sanitize all card of the list
            $composedRule = new ComposedRule();
            $composedRule->tags = [];
        }

        foreach ($cardListView->cardViews as $cardView) {
            foreach ($this->tagMapping as $tag => $equivalentVariable) {
                if (!in_array($tag, $composedRule->tags)) {
                    $cardView->$equivalentVariable = '';
                }
            }
        }
    }

    /**
     * @param CardView $cardView
     * @param array    $rules
     */
    public function applyRuleForParticipantCard(CardView &$cardView, array $rules)
    {
        try {
            $composedRule = $this->composer->compose($rules);
        } catch (NoRuleException $exception) {
            // If no rule, create a composedRule without tags to sanitize the cardView
            $composedRule = new ComposedRule();
            $composedRule->tags = [];
        }

        foreach ($this->tagMapping as $tag => $equivalentVariable) {
            if (!in_array($tag, $composedRule->tags)) {
                $cardView->$equivalentVariable = '';
            }
        }
    }
}
