<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use DateTime;
use Elastica\Exception\NotFoundException;
use Proximum\Vimeet\Domain\Model\ActivateAccountToken;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends BaseController
{
    /**
     * @param Request              $request
     * @param EventView            $eventView
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function passwordAction(Request $request, EventView $eventView, ActivateAccountToken $activateAccountToken)
    {
        if (new DateTime() > $activateAccountToken->getExpireDate()) {
            throw new NotFoundException('Date of the token expired');
        }

        return $this->render('VimeetAppBundle:Event/ActivateAccount:password.html.twig', [

        ]);
    }
}
