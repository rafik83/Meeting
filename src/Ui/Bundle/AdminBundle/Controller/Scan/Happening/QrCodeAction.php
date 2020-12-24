<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Scan\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQuery;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class QrCodeAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Request $request, Event $event, Happening $happening)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_HOST')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        /** @var QRCodeIdentifierListView $identifiers */
        $identifiers = $this->queryBus->handle(
            new GetQRCodeIdentifiersByEventQuery(
                $event,
                $event->getAvailableLocale($request->getLocale()),
                false
            )
        );

        return $this->engine->renderResponse('AdminBundle:Scan/Happening:qrcode.html.twig', [
            'happening' => $happening,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'event' => $event,
            'identifiers' => $identifiers->list,
            'date' => $this->dateTime,
        ]);
    }
}
