<?php

namespace Proximum\Vimeet\Application\Query\Badge\QRCode;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class QRCodeIdentifierToUserQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(QRCodeIdentifierToUserQuery $query): ?User
    {
        return $this->userRepository->findOneById(
            (int) substr($query->identifier, 0, QRCodeIdentifierQueryHandler::USER_PAD_LEFT)
        );
    }
}
