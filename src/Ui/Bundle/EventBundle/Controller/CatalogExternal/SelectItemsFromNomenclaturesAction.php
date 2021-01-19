<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\CatalogExternal;

use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SelectItemsFromNomenclaturesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class SelectItemsFromNomenclaturesAction
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var GetNomenclaturesByTag */
    private $getNomenclaturesByTag;

    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        GetNomenclaturesByTag $getNomenclaturesByTag
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->getNomenclaturesByTag = $getNomenclaturesByTag;
    }

    public function __invoke(Request $request, EventDomain $eventDomain, string $tag): Response
    {
        $event = $eventDomain->getEvent();
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if (null === $catalogVisibility) {
            throw new AccessDeniedException();
        }

        $nomenclatures = $this->getNomenclaturesByTag->handle($event, $tag);

        $form = $this->formFactory->create(
            SelectItemsFromNomenclaturesType::class,
            [],
            [
                'method' => 'GET',
                'locale' => $request->getLocale(),
                'nomenclatures' => $nomenclatures,
                'label' => false,
            ]
        );

        return new Response(
            $this->engine->render(
                'EventBundle:Catalog/Partial:selectItemsFromNomenclatures.html.twig',
                [
                    'event' => $event,
                    'form' => $form->createView(),
                ]
            )
        );
    }
}
