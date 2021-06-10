<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;

class CardListViewQueryHandler
{
    private CardViewQueryHandler $cardViewQueryHandler;
    private NetworkingAccessChecker $networkingAccessChecker;

    public function __construct(
        CardViewQueryHandler $cardViewQueryHandler,
        NetworkingAccessChecker $networkingAccessChecker
    ) {
        $this->cardViewQueryHandler = $cardViewQueryHandler;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    public function handle(CardListViewQuery $cardListViewQuery): CardListView
    {
        $participants = $cardListViewQuery->sheet->getParticipants();
        $user = $cardListViewQuery->user;
        $cardListView = new CardListView();

        $showMeetOnline = $this->networkingAccessChecker->isSheetAllowedToAccess($cardListViewQuery->sheet);

        foreach ($participants as $participant) {
            if ($cardListViewQuery->editable) {
                $editable = $participant->getUser() === $user || $cardListViewQuery->sheet->isOwner($user);
            } else {
                $editable = false;
            }

            $cardViewQuery = new CardViewQuery($participant, $cardListViewQuery->locale, $editable, $showMeetOnline);

            $cardListView->cardViews[$participant->getId()] = $this->cardViewQueryHandler->handle($cardViewQuery);
        }

        return $cardListView;
    }
}
