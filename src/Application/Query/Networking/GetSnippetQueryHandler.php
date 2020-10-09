<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\View\Networking\GetSnippetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class GetSnippetQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NetworkingAccessChecker $networkingAccessChecker
    )
    {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    public function handle(GetSnippetQuery $getSnippetQuery)
    {
        if (!$this->networkingAccessChecker->allowedToAccess($getSnippetQuery->sheet->getEvent())) {
            throw new ClosedNetworkingException();
        }

        $topic = $this->notificationSubscriber->getNotificationTopic($getSnippetQuery->sheet->getEvent()->getId());

        return new GetSnippetView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($getSnippetQuery->sheet, $getSnippetQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic
        );
    }
}
