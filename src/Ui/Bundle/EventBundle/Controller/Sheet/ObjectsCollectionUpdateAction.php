<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ObjectsCollectionUpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationChecker, EngineInterface $engine)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        string $locale,
        string $key
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        return new Response(
            $this->engine->render(
                '@Event/Sheet/objectsCollectionUpdate.html.twig',
                ['event' => $event, 'locale' => $locale, 'key' => $key]
            )
        );
    }
}
