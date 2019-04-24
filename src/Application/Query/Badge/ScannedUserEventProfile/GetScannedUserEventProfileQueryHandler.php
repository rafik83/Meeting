<?php

namespace Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQuery;
use Proximum\Vimeet\Domain\Model\User;

class GetScannedUserEventProfileQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function handle(GetScannedUserEventProfileQuery $query): ScannedUserEventProfileView
    {
        $user = $this->queryBus->handle(new QRCodeIdentifierToUserQuery($query->identifier));

        if (!$user instanceof User) {
            throw new UserNotFoundException('User not found');
        }

        return new ScannedUserEventProfileView($user->getFirstName(), $user->getLastName());
    }
}
