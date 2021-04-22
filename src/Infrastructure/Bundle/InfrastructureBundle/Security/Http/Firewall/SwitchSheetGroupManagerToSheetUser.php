<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Http\Firewall;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\UserChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

/**
 * Switch Sheet Group manager to Sheet user
 */
class SwitchSheetGroupManagerToSheetUser
{
    const FIREWALL_PROVIDER_KEY = 'main';
    const ROLE_PREVIOUS_USER    = 'ROLE_PREVIOUS_USER';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private UserChecker $userChecker;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserChecker $userChecker,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenStorage         = $tokenStorage;
        $this->userChecker          = $userChecker;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param User  $fromUser
     * @param Sheet $toSheet
     * @param User  $toSheetUser
     *
     * @throws AccessDeniedException
     */
    public function handle(User $fromUser, Sheet $toSheet, User $toSheetUser)
    {
        $sheetGroup = $toSheet->getGroup();

        if (!$toSheet->hasUser($toSheetUser)
            || null === $sheetGroup
            || !$this->authorizationChecker->isGranted(GroupVoter::MANAGE, $sheetGroup)
        ) {
            throw new AccessDeniedException();
        }

        try {
            $managerToken = $this->tokenStorage->getToken();

            $this->userChecker->checkPostAuth($toSheetUser);

            $roles   = $toSheetUser->getRoles();
            $roles[] = new SwitchUserRole(self::ROLE_PREVIOUS_USER, $managerToken);

            $token = new UsernamePasswordToken($toSheetUser, $toSheetUser->getPassword(), self::FIREWALL_PROVIDER_KEY, $roles);
            $this->tokenStorage->setToken($token);
        } catch (\Exception $exception) {
            throw new AccessDeniedException(
                sprintf(
                    'Switch sheet group manager to sheet user from %s to %s failed: "%s"',
                    $fromUser->getEmail(),
                    $toSheetUser->getEmail(),
                    $exception->getMessage()
                )
            );
        }
    }

    /**
     * Attempts to exit from an already switched user.
     *
     * @throws AuthenticationCredentialsNotFoundException
     */
    public function unswitch()
    {
        $originalToken = $this->getOriginalToken($this->tokenStorage->getToken());

        if (false === $originalToken) {
            throw new AuthenticationCredentialsNotFoundException('Could not find original Token object.');
        }

        $this->tokenStorage->setToken($originalToken);
    }

    /**
     * Gets the original Token from a switched one.
     *
     * @param TokenInterface $token A switched TokenInterface instance
     *
     * @return TokenInterface|false The original TokenInterface instance, false if the current TokenInterface is not switched
     */
    private function getOriginalToken(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                return $role->getSource();
            }
        }

        return false;
    }
}
