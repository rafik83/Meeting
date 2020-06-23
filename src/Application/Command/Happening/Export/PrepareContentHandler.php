<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantExportViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;

class PrepareContentHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(QueryBusInterface $queryBus, SerializerAdapterInterface $serializer)
    {
        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
    }

    public function handle(PrepareContent $prepareContent): string
    {
        $event = $prepareContent->event;
        $locale = $event->getAvailableLocale($prepareContent->locale);

        $happeningParticipantViews = $this->queryBus->handle(
            new HappeningParticipantExportViewQuery($event, $locale)
        );

        return $this->serializer->serialize(
            $happeningParticipantViews,
            'csv',
            [
                'locale' => $locale,
                'charset' => Charset::WINDOWS_1252,
                'csv_delimiter' => ';',
            ]
        );
    }
}
