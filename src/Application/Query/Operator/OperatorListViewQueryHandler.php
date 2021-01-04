<?php

namespace Proximum\Vimeet\Application\Query\Operator;

use Proximum\Vimeet\Application\View\Operator\OperatorListView;
use Proximum\Vimeet\Application\View\Operator\OperatorView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class OperatorListViewQueryHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function handle(OperatorListViewQuery $query): OperatorListView
    {
        $operators = $this->adminRepository->getOperatorForOrganizer($query->organizer, $query->filters);
        $operatorViews = [];

        foreach ($operators as $operator) {
            $operatorViews[] = new OperatorView(
                $operator->getId(),
                $operator->getLastname(),
                $operator->getFirstname(),
                $operator->getEmail(),
                $operator->getRole(),
                array_map(function (Event $event) {
                    return $event->getTitle();
                }, $operator->getEvents()->toArray()),
                $operator->getLastLoginAt()
            );
        }

        return new OperatorListView($operatorViews);
    }
}
