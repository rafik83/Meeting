<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ShowUploadedFileAction
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        string $sharedUploadedFiles
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    /**
     * Used to show file on sheet and to export MultiUploadCollectionObject
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param int         $sheetToDisplayId
     * @param string      $objectKey
     * @param string      $path
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        int $sheetToDisplayId,
        string $objectKey,
        string $path
    ): Response {
        $event = $eventDomain->getEvent();
        $sheetToDisplay = $this->sheetRepository->getSheetById($sheetToDisplayId);

        if (null === $sheetToDisplay) {
            throw new AccessDeniedException('Sheet not found');
        }

        $this->checkAccess($event, $sheetToDisplay);

        $locale = $request->getLocale();
        $templateData = $this->templateDataFactory->createFromSheet($sheetToDisplay, $locale);
        $multiUploadObject = $templateData->getObject($objectKey);

        if (!$multiUploadObject instanceof MultiUploadCollectionObject) {
            throw new AccessDeniedException(sprintf('This object %s is not a MultiUploadCollectionObject', $objectKey));
        }

        if (!$multiUploadObject->hasUpload($path)) {
            throw new AccessDeniedException(sprintf('This object %s do not contains %s path', $objectKey, $path));
        }

        $fullPath = sprintf('%s%s', $this->sharedUploadedFiles, $path);

        return new BinaryFileResponse($fullPath);
    }

    private function checkAccess(Event $event, Sheet $sheetToDisplay)
    {
        if ($event !== $sheetToDisplay->getEvent()) {
            throw new AccessDeniedException('Sheet not found');
        }
    }
}
