<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\CompletenessCalculator;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class BuilderAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        NomenclatureRepositoryInterface $nomenclatureRepository,
        CompletenessCalculator $completenessCalculator,
        FlashBagInterface $flashBag,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->completenessCalculator = $completenessCalculator;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
    }

    public function __invoke(Request $request, Event $event, FormTemplate $template, string $locale): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || $template->getEvent() !== $event
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        if (!$template->hasLocale($locale)) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist on this template', $locale));
        }

        $completeness = $this->completenessCalculator->compute($template);

        $incomplete = array_keys(array_filter($completeness, function ($percent) {
            return $percent < 100;
        }));

        $nomenclatures = $this->nomenclatureRepository->findByEvent($template->getEvent());

        // Add warning if some locales translations are incomplete
        if (!empty($incomplete)) {
            $this->flashBag->add('warning', 'flash.template.incomplete_translations.warning');
        }

        return new Response($this->engine->render('AdminBundle:Template/Form:builder.html.twig', [
            'completeness' => $completeness,
            'event' => $event,
            'locale' => $locale,
            'nomenclatures' => $nomenclatures,
            'template'  => $template,
            'uploadFormats' => UploadObject::ALLOWED_FORMATS,
            'templateTagView' => Tag::getTemplateTagView(),
        ]));
    }
}
