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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Exception\NotMultiUploadCollectonObjectException;
use Proximum\Vimeet\Domain\Template\Exception\PathNotFoundInMultiUploadCollectonObjectException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowUploadedFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        TemplateDataFactory $templateDataFactory,
        string $sharedUploadedFiles
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->templateDataFactory = $templateDataFactory;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function __invoke(
        Request $request,
        UserDomain $userDomain,
        Sheet $sheet,
        string $objectKey,
        string $path
    ): Response {
        $locale = $request->getLocale();
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
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
}
