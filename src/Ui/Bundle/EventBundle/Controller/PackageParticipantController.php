<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Add as AddParticipant;
use Proximum\Vimeet\Application\Command\Participant\Remove as RemoveParticipant;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageParticipantController extends AbstractController
{
    private AddParticipantChecker $addParticipantChecker;
    private QueryBusInterface $queryBus;

    public function __construct(
        AddParticipantChecker $addParticipantChecker,
        QueryBusInterface $queryBus
    ) {
        $this->addParticipantChecker = $addParticipantChecker;
        $this->queryBus = $queryBus;
    }

    public function addParticipantAction(
        Request $request,
        Sheet $sheet,
        int $step,
        UserDomain $userDomain
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->hasUser($userDomain->getUser())) {
            throw $this->createNotFoundException(
                sprintf(
                    'The current user %s is not associated with this sheet %s',
                    $this->getUser()->getId(),
                    $sheet->getId()
                )
            );
        }

        if (!$this->addParticipantChecker->canAddParticipant($sheet)) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $locale = $request->getLocale();
        $participantProductViews = $this->queryBus->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        $addParticipant = new AddParticipant($sheet, $locale, $userDomain->getUser(), $participantProductViews);
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'sheet'    => $sheet,
            'locale'   => $locale,
            'products' => $participantProductViews,
            'action'   => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
        ]);

        return $this->render('EventBundle:Participant:add.html.twig', [
            'form'                    => $form->createView(),
            'sheet'                   => $sheet,
            'participantProductViews' => $participantProductViews,
            'backRoute'               => 'backToPackage',
        ]);
    }

    public function removeParticipantAction(Request $request, Sheet $sheet, int $step): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (1 === $sheet->countParticipants()) {
            throw $this->createNotFoundException('Impossible to remove participants from a sheet with one participant');
        }

        $locale = $request->getLocale();
        $remove = new RemoveParticipant($sheet, $locale);
        $form   = $this->createForm(RemoveType::class, $remove, [
            'action'       => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
            'participants' => $sheet->getParticipants(),
        ]);

        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->queryBus->handle($cardListViewQuery);

        return $this->render('EventBundle:Participant:removeFromPackage.html.twig', [
            'form'         => $form->createView(),
            'participants' => $participants,
        ]);
    }
}
