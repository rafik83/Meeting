<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\CatalogExternal;

use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SelectItemsFromNomenclaturesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class SelectItemsFromNomenclaturesAction
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var GetNomenclaturesByTag */
    private $getNomenclaturesByTag;

    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        Environment $twig,
        FormFactoryInterface $formFactory,
        GetNomenclaturesByTag $getNomenclaturesByTag
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->twig = $twig;
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
            $this->twig->render(
                'EventBundle:Catalog/Partial:selectItemsFromNomenclatures.html.twig',
                [
                    'event' => $event,
                    'form' => $form->createView(),
                ]
            )
        );
    }
}
