<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
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

    /** @var string */
    private $encryptedFilesPath;

    /** @var string */
    private $webDir;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        TemplateDataFactory $templateDataFactory,
        UserEventDecryptFileInterface $userEventDecryptFile,
        string $encryptedFilesPath,
        string $webDir
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->templateDataFactory = $templateDataFactory;
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

        if ($participant instanceof Participant) {
            if ($participant->getSheet() !== $sheet) {
                throw new AccessDeniedException('Invalid sheet');
            }

            $templateData = $this->templateDataFactory->createRegistrationFromParticipant(
                $participant,
                $request->getLocale()
            );
        } else {
            $templateData = $this->templateDataFactory->createRegistrationFromSheet($sheet);
        }

        try {
            $uploadObject = $templateData->getObject($objectKey);
            if (!$uploadObject instanceof UploadObject || null === $uploadObject->getPath()) {
                throw new AccessDeniedException('Invalid object');
            }

            $downloadPath = $this->webDir . $uploadObject->getPath();

            if ($uploadObject->isCrypted()) {
                $user = $participant instanceof Participant ? $participant->getUser() : $sheet->getOwner();
                $directoryStructure = explode('/', $uploadObject->getPath());
                $downloadPath = '/tmp/' . end($directoryStructure); // Retrieve only the filename

                $this->userEventDecryptFile->decryptFile(
                    $sheet->getEvent(),
                    $user,
                    $this->encryptedFilesPath . $uploadObject->getPath(),
                    $downloadPath
                );
            }

            return (new BinaryFileResponse($downloadPath))
                ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        } catch (ObjectNotFoundException $exception) {
            throw new AccessDeniedException('Invalid object');
        }
    }
}
