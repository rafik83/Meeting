<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadExportDataAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var string */
    private $exportPath;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        string $exportPath,
        \DateTimeInterface $dateTime
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->exportPath = $exportPath;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Event $event, File $file, string $hash)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $file->getHash() !== $hash
        ) {
            throw new AccessDeniedException();
        }

        return new CsvFileResponse(
            file_get_contents(sprintf('%s%s', $this->exportPath, $file->getPath())),
            sprintf('export_form_template_%d_%s.csv', $event->getId(), $this->dateTime->format('h_i_s_d_m_Y'))
        );
    }
}
