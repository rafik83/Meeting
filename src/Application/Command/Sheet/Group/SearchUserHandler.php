<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Domain\View\Group\UserView;

class SearchUserHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /**
     * SearchUserHandler constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param UserToGroupManagerChecker $userToGroupManagerChecker
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserToGroupManagerChecker $userToGroupManagerChecker
    ) {
        $this->userRepository            = $userRepository;
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
    }

    /**
     * @param SearchUser $query
     *
     * @throws UserNotAllowedToManageGroupException
     * @throws UserNotFoundForGivenEmailException
     *
     * @return UserView
     */
    public function handle(SearchUser $query)
    {
        $user = $this->userRepository->findByEmail($query->email);

        if (!$user) {
            throw new UserNotFoundForGivenEmailException($query->email);
        }

        try {
            $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($query->event, $user);
        } catch (UserAlreadyGroupManagerOnSameEventException $exception) {
            throw new UserNotAllowedToManageGroupException($query->email);
        } catch (UserAlreadyParticipantOrOwnerOnGroupOnSameEventException $exception) {
            throw new UserNotAllowedToManageGroupException($query->email);
        }

        return new UserView($user->getId(), $user->getEmail(), $user->getFullname());
    }
}
