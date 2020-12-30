<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\View\User\UserImpersonateView;
use Proximum\Vimeet\Application\View\User\UserView;

class UserImpersonateViewQueryHandler
{
    /**
     * @var ImpersonateUrlGeneratorInterface
     */
    private $impersonateUrlGenerator;

    /**
     * UserImpersonateViewQueryHandler constructor.
     *
     * @param ImpersonateUrlGeneratorInterface $impersonateUrlGenerator
     */
    public function __construct(ImpersonateUrlGeneratorInterface $impersonateUrlGenerator)
    {
        $this->impersonateUrlGenerator = $impersonateUrlGenerator;
    }

    /**
     * @param UserImpersonateViewQuery $query
     *
     * @return UserImpersonateView
     */
    public function handle(UserImpersonateViewQuery $query)
    {
        $exitLink = $this->impersonateUrlGenerator->generateExit($query->exitRouteName, $query->exitRouteParameters);

        $parentUserView = new UserView(
            $query->previousUser->getFirstname(),
            $query->previousUser->getLastname(),
            $query->previousUser->getEmail()
        );

        $userView = new UserView(
            $query->user->getFirstName(),
            $query->user->getLastName(),
            $query->user->getEmail()
        );

        return new UserImpersonateView($parentUserView, $userView, $exitLink);
    }
}
