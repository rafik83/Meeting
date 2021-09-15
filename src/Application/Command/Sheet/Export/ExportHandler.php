<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Export;

use Proximum\Vimeet\Application\Command\File\PersistContent;
use Proximum\Vimeet\Application\Command\File\PersistContentHandler;
use Proximum\Vimeet\Application\Query\Sheet\Export\ExportQuery;
use Proximum\Vimeet\Application\Query\Sheet\Export\ExportQueryHandler;

class ExportHandler
{
    /** @var ExportQueryHandler */
    private $exportQueryHandler;

    /** @var NotifyHandler */
    private $notifyHandler;

    /** @var PersistContentHandler */
    private $persistContentHandler;

    public function __construct(
        ExportQueryHandler $exportQueryHandler,
        PersistContentHandler $persistContentHandler,
        NotifyHandler $notifyHandler
    ) {
        $this->exportQueryHandler = $exportQueryHandler;
        $this->notifyHandler = $notifyHandler;
        $this->persistContentHandler = $persistContentHandler;
    }

    public function handle(Export $export): void
    {
        $content = $this->exportQueryHandler->handle(
            new ExportQuery(
                $export->event,
                $export->locale,
                $export->sheetIds,
                $export->displayNomenclatureIds
            )
        );

        $file = $this->persistContentHandler->handle(
            new PersistContent($export->event, $content, 'export_event_sheets_%s_%s.csv')
        );


        $this->notifyHandler->handle(new Notify(
            $export->event,
            $export->admin,
            $export->locale,
            $file
        ));
    }
}
