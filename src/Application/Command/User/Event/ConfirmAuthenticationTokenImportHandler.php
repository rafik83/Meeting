<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Repository\User\Event\AuthenticationTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;

class ConfirmAuthenticationTokenImportHandler
{
    /** @var AuthenticationTokenRepositoryInterface */
    private $authenticationTokenRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        AuthenticationTokenRepositoryInterface $authenticationTokenRepository,
        UserRepositoryInterface $userRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->authenticationTokenRepository = $authenticationTokenRepository;
        $this->userRepository = $userRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(ConfirmAuthenticationTokenImport $confirmAuthenticationTokenImport): void
    {
        $authenticationTokenImports = $confirmAuthenticationTokenImport->authenticationTokenImports;

        /** @var AuthenticationTokenImport $authenticationTokenImport */
        foreach ($authenticationTokenImports as $authenticationTokenImport) {
            if ($authenticationTokenImport->hasError()) {
                continue;
            }

            $authenticationTokenImportView = $authenticationTokenImport->authenticationTokenImportView;

            $user = $this->userRepository->findByEmail($authenticationTokenImportView->email);
            if (!$user instanceof User) {
                continue;
            }

            $authenticationToken = $this->authenticationTokenRepository->findByEventAndUser(
                $authenticationTokenImportView->event,
                $user
            );

            // If already exist, replace token
            if ($authenticationToken instanceof AuthenticationToken) {
                $authenticationToken->updateTokenAndExpiredAt(
                    $authenticationTokenImportView->token,
                    $authenticationTokenImportView->expirationDate
                );

                $this->authenticationTokenRepository->set($authenticationToken);

                continue;
            }

            $this->authenticationTokenRepository->add(
                new AuthenticationToken(
                    $user,
                    $authenticationTokenImportView->event,
                    $authenticationTokenImportView->token,
                    $this->dateTime,
                    $authenticationTokenImportView->expirationDate
                )
            );
        }
    }
}
