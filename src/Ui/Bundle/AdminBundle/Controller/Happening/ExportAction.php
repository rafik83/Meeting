<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningExportViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
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
     * @param string  $locale
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|CsvFileResponse
     */
    public function __invoke(Request $request, Event $event, string $locale)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access Denied!');
        }

        try {
            /** @var HappeningExportListView $happeningExportListView */
            $happeningExportListView = $this->queryBus->handle(
                new HappeningExportViewQuery($event, $event->getAvailableLocale($locale))
            );
        } catch (EmptyHappeningException $exception) {
            $this->flashBag->add('error', 'flash.admin.happening.empty');

            return new RedirectResponse(
                $this->router->generate('admin_happening_list', ['event' => $event->getId()])
            );
        }

        $exportedContent = $this->serializer->serialize(
            $happeningExportListView->getHappeningExportListView(), 'csv', [
            'locale'        => $event->getAvailableLocale($request->getLocale()),
            'charset'       => Charset::UTF_8,
            'csv_delimiter' => ';',
        ]
        );

        return new CsvFileResponse($exportedContent, 'export_happening_'.date('Y_m_d_His').'.csv');
    }
}
