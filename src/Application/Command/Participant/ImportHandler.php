<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Serializer\Charset;

class ImportHandler
{
    /** @var FileStorageInterface */
    private $localFileStorageAdapter;

    /** @var SessionInterface */
    private $session;

    /** @var string */
    private $publicDir;

    public function __construct(
        FileStorageInterface $localFileStorageAdapter,
        SessionInterface $session,
        $publicDir
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->session = $session;
        $this->publicDir = $publicDir;
    }

    public function handle(Import $command): void
    {
        $filePath = $this->localFileStorageAdapter->upload($command->file, $this->publicDir);

        $filename = Charset::convertFile(
            $this->publicDir . $filePath,
            $command->charset,
            Charset::UTF_8,
            $this->publicDir . $filePath
        );

        $this->session->set(ParticipantImportTag::PARTICIPANT_IMPORT_FILE, $filename);
        $this->session->set(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET, $command->charset);
        $this->session->set(ParticipantImportTag::PARTICIPANT_IMPORT_ALLOW_MULTI_SHEET, $command->allowMultiSheet);
    }
}
