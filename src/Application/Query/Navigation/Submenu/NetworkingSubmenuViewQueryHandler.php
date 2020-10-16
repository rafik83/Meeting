<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;


use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class NetworkingSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        NetworkingAccessChecker $networkingAccessChecker,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatSessionRepositoryInterface $chatSessionRepository
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function handle(NetworkingSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if ($this->networkingAccessChecker->allowedToAccess($query->event)) {
            $networkingTitle = 'navigation.category.networking';

            if (isset($query->staticFormulationsIndexedByCategory[Category::NETWORKING])) {
                $networkingTitle = $query->staticFormulationsIndexedByCategory[Category::NETWORKING]->getTitle($query->locale);
            }

            $eventMessagesCount = 0;

            $isRouteNetworking = Route::isNetworking($query->route);

            if (!$isRouteNetworking) {
                $eventMessagesCount = $this->chatMessageRepository->getMessagesCountByLinkableObject(
                    $query->event,
                    $query->sheet->getUserParticipant($query->user)->getNetworkingChatViewedAt()
                );
            }

            $privateChatSessions = $this->chatSessionRepository->findSessionsByEventAndUser($query->event, $query->user);
            $privateChatSessionsCount = array_reduce($privateChatSessions, static function ($carry, $chatSession) use ($query) {
                return $carry + ($chatSession['unreadMessages'][$query->user->getId()] ?? 0);
            }, 0);

            return new SubmenuButtonView(
                Category::NETWORKING_ICON,
                $networkingTitle,
                $this->navigationBuilder->getRoute('event_networking_index', ['sheet' => $query->sheet->getId()]),
                $isRouteNetworking,
                $eventMessagesCount + $privateChatSessionsCount,
                true
            );
        }
        return null;
    }
}
