<?php

namespace Proximum\Vimeet\Application\Query\Admin;

use Proximum\Vimeet\Application\View\Admin\AdminListView;
use Proximum\Vimeet\Application\View\Admin\AdminView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminListViewQueryHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function handle(AdminListViewQuery $query): AdminListView
    {
        $admins = $this->adminRepository->list($query->filters);

        $adminViews = [];
        foreach ($admins as $admin) {
            $adminViews[] = new AdminView(
                $admin->getId(),
                $admin->getLastname(),
                $admin->getFirstname(),
                $admin->getEmail(),
                $admin->getRole(),
                array_map(function (Event $event) {
                    return $event->getTitle();
                }, $admin->getEvents()->toArray())
            );
        }

        return new AdminListView($adminViews);
    }
}
