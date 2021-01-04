<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Template\Object\UploadObjectDownloadPathGetter;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Templating\EngineInterface;

class DownloadFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var DataUriNormalizer */
    private $dataUriNormalizer;

    /** @var EngineInterface */
    private $engine;

    /** @var UploadObjectDownloadPathGetter */
    private $uploadObjectDownloadPathGetter;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        DataUriNormalizer $dataUriNormalizer,
        EngineInterface $engine,
        UploadObjectDownloadPathGetter $uploadObjectDownloadPathGetter,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dataUriNormalizer = $dataUriNormalizer;
        $this->engine = $engine;
        $this->uploadObjectDownloadPathGetter = $uploadObjectDownloadPathGetter;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        string $objectKey,
        FormTemplate $formTemplate,
        Participant $participant,
        bool $preview = false
    ): Response {
        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || false === $this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$formTemplate->isPublished()
            || !$formTemplate->hasType($sheet->getType())
        ) {
            throw new AccessDeniedException();
        }

        $templateData = $this->queryBus->handle(
            new FormTemplateDataQuery($formTemplate, $sheet, $participant, $request->getLocale())
        );

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
        }
    }
}
