<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Template\Object\UploadObjectDownloadPathGetter;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Exception\FileNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Twig\Environment;

class DownloadFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var DataUriNormalizer */
    private $dataUriNormalizer;

    private Environment $engine;

    /** @var UploadObjectDownloadPathGetter */
    private $uploadObjectDownloadPathGetter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        TemplateDataFactory $templateDataFactory,
        DataUriNormalizer $dataUriNormalizer,
        Environment $engine,
        UploadObjectDownloadPathGetter $uploadObjectDownloadPathGetter
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->templateDataFactory = $templateDataFactory;
        $this->dataUriNormalizer = $dataUriNormalizer;
        $this->engine = $engine;
        $this->uploadObjectDownloadPathGetter = $uploadObjectDownloadPathGetter;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        string $objectKey,
        Participant $participant = null,
        bool $preview = false
    ): Response {
        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ||
            false === $this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $templateData = $this->createTemplateData($sheet, $request->getLocale(), $participant);

        try {
            $downloadPath = $this->uploadObjectDownloadPathGetter->getDownloadPath(
                $templateData,
                $objectKey,
                $sheet,
                $participant
            );

            if (true === $preview) {
                return new Response($this->engine->render('@Event/base64Image.html.twig', [
                    'file' => $this->dataUriNormalizer->normalize(new \SplFileInfo($downloadPath)),
                ]));
            }

            return (new BinaryFileResponse($downloadPath))
                ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        } catch (ObjectNotFoundException $exception) {
            throw new AccessDeniedException('Invalid object');
        } catch (FileNotFoundException $exception) {
            if (true === $preview) {
                return new Response('');
            }

            throw new NotFoundHttpException('File not found');
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
