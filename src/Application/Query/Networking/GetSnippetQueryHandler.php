<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\View\Networking\GetSnippetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;

class GetSnippetQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NetworkingAccessChecker $networkingAccessChecker
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    /**
     * @throws NetworkingNotAccessibleException
     */
    public function handle(GetSnippetQuery $getSnippetQuery): GetSnippetView
    {
        if (!$this->networkingAccessChecker->isSheetAllowedToAccess($getSnippetQuery->sheet)) {
            throw new NetworkingNotAccessibleException();
        }

        return new GetSnippetView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getUserSubscriberKey($getSnippetQuery->sheet, $getSnippetQuery->user),
            $this->notificationSubscriber->getUserTopic($getSnippetQuery->sheet->getEvent()->getId(), $getSnippetQuery->user->getId())
        );
    }
}
