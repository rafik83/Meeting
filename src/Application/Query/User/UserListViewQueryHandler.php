<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\View\User\UserListView;

class UserListViewQueryHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * UserListViewQueryHandler constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param UserListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(UserListViewQuery $query)
    {
        $paginatedResult = $this->userRepository->paginate(
            $query->page,
            20,
            $query->event,
            $query->filters,
            $query->locale
        );

        $userListViews = [];

        foreach ($paginatedResult->results as $result) {
            $userListViews[] = new UserListView(
                $result['id'],
                $result['email'],
                $result['firstname'],
                $result['lastname'],
                $result['typeTitle'],
                !empty($result['sheetId']) ? $result['sheetId'] : null,
                !empty($result['sheetTypeId']) ? $result['sheetTypeId'] : null,
                !empty($result['sheetTypeTitle']) ? $result['sheetTypeTitle'] : null
            );
        }

        $paginatedResult->results = $userListViews;

        return $paginatedResult;
    }
}
