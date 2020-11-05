<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollection;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollectionHandler;
use Proximum\Vimeet\Application\Command\Sheet\Upload\VideoUpload;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfosHelper;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Update an object and display the sheet with the modal in case of form error.
 */
class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CreateObjectFormHandler */
    private $createObjectFormHandler;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var MultiUploadCollectionHandler */
    private $multiUploadCollectionHandler;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var SheetInfosHelper */
    private $sheetInfosHelper;

    /** @var AddParticipantChecker */
    private $addParticipantChecker;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        TemplateDataFactory $templateDataFactory,
        TemplateProductGuesser $templateProductGuesser,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        CreateObjectFormHandler $createObjectFormHandler,
        FileStorageInterface $fileStorage,
        MultiUploadCollectionHandler $multiUploadCollectionHandler,
        RouterInterface $router,
        EngineInterface $engine,
        SheetInfosHelper $sheetInfosHelper,
        AddParticipantChecker $addParticipantChecker
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->templateDataFactory = $templateDataFactory;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->createObjectFormHandler = $createObjectFormHandler;
        $this->fileStorage = $fileStorage;
        $this->multiUploadCollectionHandler = $multiUploadCollectionHandler;
        $this->router = $router;
        $this->engine = $engine;
        $this->sheetInfosHelper = $sheetInfosHelper;
        $this->addParticipantChecker = $addParticipantChecker;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        $locale,
        $key
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $user = $userDomain->getUser();
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $levelsArchitecture = [];

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw new NotFoundHttpException(sprintf('The given key %s is not found', $key));
        }

        if ($object instanceof Template\TemplateObject\Nomenclature) {
            $nomenclature = $object->getNomenclatureModel();
            $depth = $nomenclature->getDepth();

            if (2 === $depth || 3 === $depth) {
                $levelsArchitecture = $nomenclature->getLevelsArchitecture($locale);
            }
        }

        $products = $this->templateProductGuesser->getProducts(
            $object,
            $sheet->getPackage()
        );

        $object->setBuyableProducts($products);
        $object->setSheet($sheet);

        $savedObject = null;
        if ($object instanceof Template\TemplateObject\MultiUploadCollectionObject) {
            $savedObject = clone $object;
        }

        $templateObjectView = $this
            ->queryBus
            ->handle(new TemplateObjectViewQuery($sheet, $locale, $key));

        $form = $this->createObjectFormHandler->handle(new CreateObjectForm($object, $locale, $key));

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                if ($object instanceof Template\TemplateObject\Image) {
                    $file = $form->get('file')->getData();

                    if ($file instanceof UploadedFile) {
                        $image = $object->getImage();
                        $fileStorage = $this->fileStorage;

                        if (null !== $image) {
                            $fileStorage->remove($image);
                        }

                        $newImage = $fileStorage->upload($file);
                        $object->setImage($newImage);
                    }
                }

                if ($object instanceof Template\TemplateObject\MultiUploadCollectionObject) {
                    $objectData = $this
                        ->multiUploadCollectionHandler
                        ->handle(new MultiUploadCollection($savedObject, $object))
                    ;
                    $object->setData($objectData);
                }

                if ($object instanceof Template\TemplateObject\Video) {
                    $objectData = $this
                        ->commandBus
                        ->handle(new VideoUpload($eventDomain->getEvent(), $object))
                    ;

                    $object->setData(array_merge($object->getData(), $objectData));
                }

                $this->commandBus->handle(new UpdateData($sheet, $templateData, $object));

                return new RedirectResponse(
                    $this->router->generate(
                        'event_sheet_locale',
                        ['sheet' => $sheet->getId(), 'locale' => $locale]
                    )
                );
            } else {
                // If the form is not valid, re-render the templateData
                $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
            }
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        list($nomenclatures, $participants, $taggedData) = $this->sheetInfosHelper->getInfos(
            $sheet,
            $user,
            $locale
        );
        $label = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getLocaleFallback());

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        $twig = 'nomenclature' === $object->getType()
            ? 'EventBundle:Sheet:nomenclatures.html.twig'
            : 'EventBundle:Sheet:sheet.html.twig';

        $canAddParticipant = $this->addParticipantChecker->canAddParticipant($sheet);

        return new Response(
            $this->engine->render($twig, [
                'canAddParticipant' => $canAddParticipant,
                'event' => $eventDomain->getEvent(),
                'form' => $form->createView(),
                'label' => $label,
                'levelsArchitecture' => $levelsArchitecture,
                'locale' => $locale,
                'nomenclatures' => $nomenclatures,
                'object' => $object,
                'participants' => $participants,
                'sheet' => $sheet,
                'taggedData' => $taggedData,
                'templateData' => $templateData,
                'uid' => $key,
                'currency' => $eventDomain->getEvent()->getCurrency(),
                'vatMode' => $eventDomain->getEvent()->getMode(),
                'isRequestMeetingEnabled' => false,
                'isCatalog' => false,
                'tipTranslationViews' => $tipTranslationViews,
                'templateObjectView' => $templateObjectView,
                'isPhoneValidationRequired' => false,
            ])
        );
    }
}
