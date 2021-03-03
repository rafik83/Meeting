<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SelectItemsFromNomenclaturesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class SelectItemsFromNomenclaturesAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CatalogAccessChecker */
    private $catalogAccessChecker;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var GetNomenclaturesByTag */
    private $getNomenclaturesByTag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CatalogAccessChecker $catalogAccessChecker,
        Environment $twig,
        FormFactoryInterface $formFactory,
        GetNomenclaturesByTag $getNomenclaturesByTag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->getNomenclaturesByTag = $getNomenclaturesByTag;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain,
        string $tag
    ): Response {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$sheet->isInCatalog()
            || !$this->catalogAccessChecker->allowedToAccess($event)
        ) {
            throw new AccessDeniedException('Access denied!');
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
