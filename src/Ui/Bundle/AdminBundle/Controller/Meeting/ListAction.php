<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting\SlotChoiceType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        MeetingSlotRepositoryInterface $slotRepository,
        FormFactoryInterface $formFactory,
        \DateTimeInterface $dateTime
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->slotRepository = $slotRepository;
        $this->formFactory = $formFactory;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $slots = $this->slotRepository->findByEvent($event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $defaultSlot = $this->getDefaultSlot($event, $slots);
        $form = $this->formFactory->create(SlotChoiceType::class, ['slot' => $defaultSlot], [
            'slots' => $slots,
            'method' => 'GET',
            'csrf_protection' => false,
            'timeZone' => $event->getTimeZone(),
            'locale' => $locale,
        ]);

        $currentSlot = $defaultSlot;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $currentSlot = $form->getData()['slot'];
        }

        $meetingListView = $this->queryBus->handle(
            new MeetingListViewQuery($event, $locale, $currentSlot)
        );

        return $this->engine->renderResponse('AdminBundle:Meeting:list.html.twig', [
            'event' => $event,
            'locale' => $locale,
            'view' => $meetingListView,
            'form' => $form->createView(),
            'hasPrevious' => !empty($slots) && reset($slots) !== $currentSlot,
            'hasNext' => !empty($slots) && end($slots) !== $currentSlot,
            'currentSlot' => $currentSlot,
        ]);
    }

    private function getDefaultSlot(Event $event, array $slots): ?MeetingSlot
    {
        if ($event->hasDay()) {
            $slot = $this->getCurrentSlot($slots);

            if ($slot instanceof MeetingSlot) {
                return $slot;
            }
        }

        if (!empty($slots)) {
            $slot = reset($slots);

            return $slot;
        }

        return null;
    }

    /**
     * @param MeetingSlot[] $slots
     */
    private function getCurrentSlot(array $slots): ?MeetingSlot
    {
        $previousSlot = null;

        foreach ($slots as $slot) {
            if ($slot->getBegin() <= $this->dateTime && $slot->getEnd() >= $this->dateTime) {
                return $slot;
            }

            $currentDateTimeIsBetweenPreviousAndCurrentSlot = $previousSlot instanceof MeetingSlot
                && $previousSlot->getEnd() < $this->dateTime
                && $this->dateTime < $slot->getBegin();

            if ($currentDateTimeIsBetweenPreviousAndCurrentSlot) {
                return $slot;
            }

            $previousSlot = $slot;
        }

        return null;
    }
}
