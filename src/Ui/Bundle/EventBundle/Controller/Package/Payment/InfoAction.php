<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package\Payment;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class InfoAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter, EngineInterface $engine)
    {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException('Access denied!');
        }

        return new Response($this->engine->render('EventBundle:Sheet:paymentInfo.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'sheet'  => $sheet,
            'locale' => $request->getLocale(),
        ]));
    }
}
