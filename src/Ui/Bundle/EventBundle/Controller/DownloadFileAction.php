<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\RemoveDecryptedFileEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var UserEventDecryptFileInterface */
    private $userEventDecryptFile;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var string */
    private $encryptedFilesPath;

    /** @var string */
    private $webDir;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        TemplateDataFactory $templateDataFactory,
        UserEventDecryptFileInterface $userEventDecryptFile,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        string $encryptedFilesPath,
        string $webDir
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->templateDataFactory = $templateDataFactory;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->userEventDecryptFile = $userEventDecryptFile;
        $this->encryptedFilesPath = $encryptedFilesPath;
        $this->webDir = $webDir;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        string $objectKey,
        Participant $participant = null
    ): BinaryFileResponse {
        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ||
            false === $this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $templateData = $this->createTemplateData($sheet, $request->getLocale(), $participant);

        try {
            $uploadObject = $templateData->getObject($objectKey);
            if (!$uploadObject instanceof UploadObject || null === $uploadObject->getPath()) {
                throw new AccessDeniedException('Invalid object');
            }

            $downloadPath = $this->webDir . $uploadObject->getPath();

            if ($uploadObject->isCrypted()) {
                $user = $participant instanceof Participant ? $participant->getUser() : $sheet->getOwner();
                $directoryStructure = explode('/', $uploadObject->getPath());
                $filename = sprintf('decrypted_%s', end($directoryStructure));
                $downloadPath = $this->encryptedFilesPath . $filename;

                $this->userEventDecryptFile->decryptFile(
                    $sheet->getEvent(),
                    $user,
                    $this->encryptedFilesPath . $uploadObject->getPath(),
                    $downloadPath
                );

                $this->delayedEventDispatcher->dispatch(
                    Events::REMOVE_DECRYPTED_FILE,
                    new RemoveDecryptedFileEvent($downloadPath)
                );
            }

            return (new BinaryFileResponse($downloadPath))
                ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        } catch (ObjectNotFoundException $exception) {
            throw new AccessDeniedException('Invalid object');
        }
    }

    private function createTemplateData(Sheet $sheet, string $locale, Participant $participant = null): TemplateData
    {
        if ($participant instanceof Participant) {
            if ($participant->getSheet() !== $sheet) {
                throw new AccessDeniedException('Invalid sheet');
            }

            $templateData = $this->templateDataFactory->createRegistrationFromParticipant(
                $participant,
                $locale
            );
        } else {
            $templateData = $this->templateDataFactory->createRegistrationFromSheet($sheet);
        }

        return $templateData;
    }
}
