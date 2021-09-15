<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Message;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\Message\DiscussionMeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;

class DiscussionMeetingRequestViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var MessageMeetingRequestViewQueryHandler
     */
    private $messageHandler;

    /**
     * @param SheetInfoGuesser                      $sheetInfoGuesser
     * @param MessageRepositoryInterface            $messageRepository
     * @param MessageMeetingRequestViewQueryHandler $messageHandler
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        MessageRepositoryInterface $messageRepository,
        MessageMeetingRequestViewQueryHandler $messageHandler
    ) {
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->messageRepository = $messageRepository;
        $this->messageHandler    = $messageHandler;
    }

    /**
     * @param DiscussionMeetingRequestViewQuery $query
     *
     * @return DiscussionMeetingRequestView
     */
    public function handle(DiscussionMeetingRequestViewQuery $query)
    {
        $discussion = new DiscussionMeetingRequestView();

        $messages = $this->messageRepository->getMessagesByMeetingRequest($query->meetingRequest);

        $sheetFrom = $this->sheetInfoGuesser->guessSheetTitle($query->meetingRequest->getFromSheet(), $query->locale);
        $sheetTo   = $this->sheetInfoGuesser->guessSheetTitle($query->meetingRequest->getToSheet(), $query->locale);

        /** @var Meeting\Message $message */
        foreach ($messages as $message) {
            $discussion->addMessage(
                $this->messageHandler->handle(
                    new MessageMeetingRequestViewQuery(
                        $query->meetingRequest,
                        $message,
                        $message->getFrom() === $query->meetingRequest->getFromSheet() ? $sheetFrom : $sheetTo
                    )
                )
            );
        }

        return $discussion;
    }
}
