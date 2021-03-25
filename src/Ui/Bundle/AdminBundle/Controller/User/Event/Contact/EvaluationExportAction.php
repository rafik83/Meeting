<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\Contact;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserContactEvaluationViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EvaluationExportAction
{
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private QueryBusInterface $queryBus;
    private SerializerAdapterInterface $serializer;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        SerializerAdapterInterface $serializer
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Access Denied!');
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $exportedContent = $this->serializer->serialize(
            $this->queryBus->handle(new UserContactEvaluationViewQuery($event, $locale)),
            'csv',
            [
                'locale' => $locale,
                'charset' => Charset::WINDOWS_1252,
                'csv_delimiter' => ';',
            ]
        );

         return new CsvFileResponse(
            Charset::convertString($exportedContent),
            'export_user_contact_evaluation_' . date('Y_m_d_His') . '.csv'
        );
    }
}
