<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\StayView;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class RoomingListViewQueryHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var UserViewQueryHandler */
    private $userViewQueryHandler;

    public function __construct(StayRepositoryInterface $stayRepository, UserViewQueryHandler $userViewQueryHandler)
    {
        $this->stayRepository = $stayRepository;
        $this->userViewQueryHandler = $userViewQueryHandler;
    }

    public function handle(RoomingListViewQuery $query): RoomingListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);
        $userViews = [];
        $stayViews = [];
        $stays = $this->stayRepository->getStaysByEvent($query->event);

        foreach ($stays as $stay) {
            $userStayViews = [];

            foreach ($stay->getUsers() as $user) {
                $userId = $user->getId();

                if (!isset($userViews[$userId])) {
                    $userViews[$userId] = $this->userViewQueryHandler->handle(
                        new UserViewQuery($query->event, $user, $locale)
                    );
                }

                $userStayViews[$userId] = $userViews[$userId];
            }

            $arrival = (new \DateTime())
                ->setTimestamp($stay->getArrival()->getTimestamp())
            ;
            $departure = (new \DateTime())
                ->setTimestamp($stay->getDeparture()->getTimestamp())
            ;

            $stayViews[] = new StayView(
                $stay->getAccommodation()->getTitle(),
                $arrival->format('d/m/Y'),
                $departure->format('d/m/Y'),
                $stay->getRoomType(),
                $stay->getRoomNumber(),
                $userStayViews
            );
        }

        return new RoomingListView($query->locale, $stayViews);
    }
}
