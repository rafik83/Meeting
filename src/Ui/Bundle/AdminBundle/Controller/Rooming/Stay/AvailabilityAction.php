<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQuery;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetSheetUsers;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AvailabilityAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var HasStayForPeriod */
    private $hasStayForPeriod;

    public function __construct(QueryBusInterface $queryBus, HasStayForPeriod $hasStayForPeriod)
    {
        $this->queryBus = $queryBus;
        $this->hasStayForPeriod = $hasStayForPeriod;
    }

    public function __invoke(Request $request, Event $event, User $user): JsonResponse
    {
        $arrival = $request->get('arrivalDate', null);
        $departure = $request->get('departureDate', null);

        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $arrival);
        $departureDate = \DateTime::createFromFormat('d/m/Y', $departure);

        if (!$arrival || !$departure || !$arrivalDate || !$departureDate || $departureDate <= $arrivalDate ) {
            throw new BadRequestHttpException();
        }

        /** @var User[] $users */
        $users = $this->queryBus->handle(new GetSheetUsers($user, $event));
        $roommates = [];

        foreach ($users as $otherUser) {
            $roommates[$otherUser->getId()] = [
                'label' => $otherUser->getFullname(),
                'disabled' => $this->hasStayForPeriod->isSatisfiedBy(
                    $event,
                    $otherUser,
                    $arrivalDate,
                    $departureDate
                ),
            ];
        }

        /** @var Accommodation[] $accommodations */
        $accommodations = $this->queryBus->handle(
            new AccommodationListByPeriodQuery($event, $arrivalDate, $departureDate)
        );

        $accommodationsIndexed = [];
        foreach ($accommodations as $accommodation) {
            $accommodationsIndexed[$accommodation->getId()] = $accommodation->getTitle();
        }

        return new JsonResponse([
            'accommodations' => $accommodationsIndexed,
            'roommates' => $roommates
        ]);
    }
}
