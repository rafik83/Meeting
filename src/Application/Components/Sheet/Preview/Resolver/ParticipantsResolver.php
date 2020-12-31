<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver;

use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class ParticipantsResolver
{
    /** @var CardViewQueryHandler */
    private $cardViewQueryHandler;

    /** @var Applyer */
    private $ruleApplyer;

    /**
     * @param CardViewQueryHandler $cardViewQueryHandler
     * @param Applyer              $ruleApplyer
     */
    public function __construct(CardViewQueryHandler $cardViewQueryHandler, Applyer $ruleApplyer)
    {
        $this->cardViewQueryHandler = $cardViewQueryHandler;
        $this->ruleApplyer = $ruleApplyer;
    }

    /**
     * @param Sheet                      $sheet
     * @param string                     $locale
     * @param TemplateObject\Participant $participantObject
     * @param ComposedRule[]             $rules
     *
     * @return PreviewView
     */
    public function handle(
        Sheet $sheet,
        string $locale,
        TemplateObject\Participant $participantObject,
        array $rules = []
    ): PreviewView {
        $participants = $sheet->getParticipants()->toArray();
        $cardViews = [];
        $participantsShown = 0;

        // Create card view for each participant limited by the number of participant shown
        foreach ($participants as $participant) {
            if ($participantsShown === $participantObject->getNumberOfParticipantShown()) {
                break;
            }

            $cardView = $this->cardViewQueryHandler->handle(
                new CardViewQuery($participant, $locale)
            );

            if (!empty($rules)) {
                $this->ruleApplyer->applyRuleForParticipantCard($cardView, $rules);
            }

            $cardViews[] = $cardView;
            ++$participantsShown;
        }

        return new PreviewView($participantObject->getKey(), '', $participantObject->getType(), $cardViews);
    }
}
