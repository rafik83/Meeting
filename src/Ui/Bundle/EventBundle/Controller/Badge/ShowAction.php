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
use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var QRCodeGeneratorInterface */
    private $qrCodeGenerator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QRCodeGeneratorInterface $qrCodeGenerator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->qrCodeGenerator = $qrCodeGenerator;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant
    ): Response {
        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $userToken = $event->getId() . $participant->getUser()->getId();

        return new Response(
            $this->engine->render(
                'EventBundle:Badge:show.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'qrCode' => $this->qrCodeGenerator->generateBase64Image($userToken),
                    'userToken' => $userToken,
                ]
            )
        );
    }
}
