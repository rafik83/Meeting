<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportParticipantAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var QueryBusInterface */
    private $queryBus;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     * @param QueryBusInterface                    $queryBus
     * @param SerializerAdapterInterface           $serializerAdapter
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        QueryBusInterface $queryBus,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->queryBus = $queryBus;
        $this->router = $router;
        $this->serializer = $serializerAdapter;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|CsvFileResponse
     */
    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access Denied!');
        }

        try {
            $happeningParticipantViews = $this->queryBus->handle(
                new HappeningParticipantViewQuery($event, $event->getAvailableLocale($request->getLocale()))
            );
        } catch (EmptyHappeningParticipationException $exception) {
            $this->flashBag->add('error', 'flash.admin.happening.participation.empty');

            return new RedirectResponse(
                $this->router->generate('admin_happening_list', ['event' => $event->getId()])
            );
        }

        $exportedContent = $this->serializer->serialize($happeningParticipantViews, 'csv', [
            'locale'        => $event->getAvailableLocale($request->getLocale()),
            'charset'       => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        return new CsvFileResponse(
            $exportedContent,
            'export_happening_participants_' . date('Y_m_d_His') . '.csv'
        );
    }
}
