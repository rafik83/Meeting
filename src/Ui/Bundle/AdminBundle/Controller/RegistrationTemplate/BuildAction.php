<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\AddLocale;
use Proximum\Vimeet\Application\Components\Sheet\Template\CompletenessCalculator;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\AddLocaleType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BuildAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var RouterInterface */
    private $router;

    /** @var bool */
    private $featureRegistrationTemplateBuilderEnabled;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CompletenessCalculator $completenessCalculator,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        NomenclatureRepositoryInterface $nomenclatureRepository,
        RouterInterface $router,
        bool $featureRegistrationTemplateBuilderEnabled
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->completenessCalculator = $completenessCalculator;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->router = $router;
        $this->featureRegistrationTemplateBuilderEnabled = $featureRegistrationTemplateBuilderEnabled;
    }

    public function __invoke(Request $request, RegistrationTemplate $registrationTemplate, string $locale): Response
    {
        if (!$this->featureRegistrationTemplateBuilderEnabled) {
            return new RedirectResponse(
                $this->router->generate(
                    'admin_template_registration_json',
                    [
                        'registrationTemplate' => $registrationTemplate->getId(),
                        'locale'               => $locale,
                    ]
                )
            );
        }

        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationChecker->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
        ) {
            throw new AccessDeniedException();
        }

        $addLocaleForm = null;

        if (!$registrationTemplate->getEvent()) {
            $addLocaleForm = $this->formFactory->create(
                AddLocaleType::class,
                new AddLocale($registrationTemplate),
                [
                    'action'   => $this->router->generate(
                        'admin_template_registration_add_locale',
                        ['template' => $registrationTemplate->getId()]
                    ),
                    'submit'   => true,
                    'template' => $registrationTemplate,
                ]
            );
        }

        $nomenclatures = $registrationTemplate->getEvent() ?
            $this->nomenclatureRepository->findByEvent($registrationTemplate->getEvent()) :
            $this->nomenclatureRepository->findGlobals();

        return $this->engine->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
            'addLocaleForm' => $addLocaleForm ? $addLocaleForm->createView() : null,
            'completeness' => $this->completenessCalculator->compute($registrationTemplate),
            'event' => $registrationTemplate->getEvent(),
            'locale' => $locale,
            'uploadFormats' => UploadObject::ALLOWED_FORMATS,
            'nomenclatures' => $nomenclatures,
            'registrationTemplate' => $registrationTemplate,
            'registrationTemplateTagView' => Tag::getRegistrationTemplateTagView(),
        ]);
    }
}
