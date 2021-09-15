<?php

namespace Proximum\Vimeet\Application\Components\Planning\Displayer;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantPlanningDisplayer
{
    /**
     * @var ParticipantPlanningFormatter
     */
    private $participantPlanningFormatter;

    /**
     * @var MarkdownAdapterInterface
     */
    private $markdown;

    /**
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param MarkdownAdapterInterface     $markdown
     */
    public function __construct(
        ParticipantPlanningFormatter $participantPlanningFormatter,
        MarkdownAdapterInterface $markdown
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->markdown                     = $markdown;
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     *
     * @return string
     */
    public function display(Event $event, User $user, $locale)
    {
        $planningMarkdown = $this
            ->participantPlanningFormatter
            ->formatPlanningFromUserAndEventWithUnallocated($user, $event, $locale);

        return $this->markdown->toHtml($planningMarkdown);
    }

    /**
     * To optimize the use of this service for multiple user
     * This method can be called to preload the users data before
     * And avoid multiple unit call for each participant
     *
     * @param User[] $users
     * @param Event  $event
     */
    public function preloadForUsersAndEvent(array $users, Event $event): void
    {
        $this->participantPlanningFormatter->preloadPlanningHandlerForUsersAndEvent($users, $event);
    }
}
