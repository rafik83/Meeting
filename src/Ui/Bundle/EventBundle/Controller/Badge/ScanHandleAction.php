<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile\GetScannedUserEventProfileQuery;
use Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile\UserNotFoundException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScanHandleAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var HasAccessToSheet */
    private $hasAccessToSheet;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        HasAccessToSheet $hasAccessToSheet,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->hasAccessToSheet = $hasAccessToSheet;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->hasAccessToSheet->isSatisfiedBy($user, $event, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['identifier'])) {
            return new JsonResponse('Bad parameters', 400);
        }

        try {
            $scannedUserEventProfileView = $this->queryBus->handle(
                new GetScannedUserEventProfileQuery($event, $data['identifier'])
            );
        } catch (UserNotFoundException $userNotFoundException) {
            return new JsonResponse('User not found', 404);
        }

        return new JsonResponse($scannedUserEventProfileView);
    }
}
