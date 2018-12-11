<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\NotMultiUploadCollectonObjectException;
use Proximum\Vimeet\Domain\Template\Exception\PathNotFoundInMultiUploadCollectonObjectException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ShowUploadedFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        RuleRepositoryInterface $ruleRepository,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        string $sharedUploadedFiles
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->ruleRepository = $ruleRepository;
        $this->sheetRepository = $sheetRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        int $sheetToDisplayId,
        string $objectKey,
        string $path
    ): Response {
        $event = $eventDomain->getEvent();
        $sheetToDisplay = $this->sheetRepository->getSheetById($sheetToDisplayId);

        if (null === $sheetToDisplay) {
            throw new AccessDeniedException('Sheet not found');
        }

        $this->checkAccess($event, $sheet, $sheetToDisplay);

        $locale = $request->getLocale();
        $templateData = $this->templateDataFactory->createFromSheet($sheetToDisplay, $locale);
        $multiUploadObject = $templateData->getObject($objectKey);

        if (!$multiUploadObject instanceof MultiUploadCollectionObject) {
            throw new NotMultiUploadCollectonObjectException(
                sprintf('This object %s is not a MultiUploadCollectionObject', $objectKey)
            );
        }

        if (!$multiUploadObject->hasUpload($path)) {
            throw new PathNotFoundInMultiUploadCollectonObjectException(
                sprintf('This object %s do not contains %s path', $objectKey, $path)
            );
        }

        $fullPath = sprintf('%s/%s', $this->sharedUploadedFiles, $path);

        return new BinaryFileResponse($fullPath);
    }

    private function checkAccess(Event $event, Sheet $sheet, Sheet $sheetToDisplay)
    {
        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        if ($event !== $sheet->getEvent()) {
            throw new AccessDeniedException('Sheet not in this event');
        }

        if (!$sheet->isInInternalCatalog()) {
            throw new AccessDeniedException('Sheet not in catalog');
        }

        if ($event !== $sheetToDisplay->getEvent()) {
            throw new AccessDeniedException('Sheet not found');
        }

        if (!$sheetToDisplay->isInInternalCatalog()) {
            throw new AccessDeniedException('Sheet to display not in catalog');
        }

        if (!$this->canSeeSheet($sheet, $sheetToDisplay)) {
            throw new AccessDeniedException('SheetToDisplay not visible by Sheet');
        }
    }

    private function canSeeSheet(Sheet $fromSheet, Sheet $sheetToDisplay): bool
    {
        if ($fromSheet->getId() === $sheetToDisplay->getId()) {
            return true;
        }

        $rules = $this->ruleRepository->getBySeerTypeAndSeeableType($fromSheet->getType(), $sheetToDisplay->getType());

        return !empty($rules);
    }
}
