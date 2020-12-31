<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetKnownRegistrationsHandler;

class ExportHandler
{
    /** @var GetKnownRegistrationsHandler */
    private $getKnownRegistrationsHandler;

    /** @var PrepareSpotsContentHandler */
    private $prepareSpotsContentHandler;

    /** @var CreateFileHandler */
    private $createFileHandler;

    /** @var NotifyHandler */
    private $notifyHandler;

    public function __construct(
        GetKnownRegistrationsHandler $getKnownRegistrationsHandler,
        PrepareSpotsContentHandler $prepareSpotsContentHandler,
        CreateFileHandler $createFileHandler,
        NotifyHandler $notifyHandler
    ) {
        $this->getKnownRegistrationsHandler = $getKnownRegistrationsHandler;
        $this->prepareSpotsContentHandler = $prepareSpotsContentHandler;
        $this->createFileHandler = $createFileHandler;
        $this->notifyHandler = $notifyHandler;
    }

    public function handle(Export $command): void
    {
        $rawRegistrationDataIndexedBySheetId = $this->getKnownRegistrationsHandler->handle($command->event);

        $content = $this->prepareSpotsContentHandler->handle(
            new PrepareSpotsContent($rawRegistrationDataIndexedBySheetId)
        );

        $file = $this->createFileHandler->handle(new CreateFile($command->event, $content));

        $this->notifyHandler->handle(new Notify($command->event, $command->admin, $file));
    }
}
