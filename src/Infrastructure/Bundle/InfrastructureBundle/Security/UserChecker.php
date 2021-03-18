<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Exception\SheetDisabledException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserChecker as SymfonyUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends SymfonyUserChecker
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * UserChecker constructor.
     *
     * @param RequestStack             $requestStack
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param TypeRepositoryInterface  $typeRepository
     * @param TypeResolver             $typeResolver
     * @param Session                  $session
     */
    public function __construct(
        RequestStack $requestStack,
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        TypeRepositoryInterface $typeRepository,
        TypeResolver $typeResolver,
        Session $session
    ) {
        $this->eventRepository     = $eventRepository;
        $this->sheetRepository     = $sheetRepository;
        $this->requestStack        = $requestStack;
        $this->session             = $session;
        $this->typeRepository      = $typeRepository;
        $this->typeResolver        = $typeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);

        $event = $this->eventRepository->getEventByDomain($this->requestStack->getCurrentRequest()->getHost());

        if (null === $event || !$user instanceof User) {
            return;
        }

        $this->checkSheetDisabled($user, $event);
        $this->checkUserType($user, $event);
    }

    /**
     * @param User  $user
     * @param Event $event
     */
    private function checkSheetDisabled(User $user, Event $event)
    {
        $sheets = $this->sheetRepository->getAllSheetsByUserAndEvent($user, $event);

        if (empty($sheets)) {
            return;
        }

        $disabledSheets = array_filter($sheets, function (Sheet $sheet) {
            return !$sheet->isEnabled();
        });

        if (count($sheets) === count($disabledSheets)) {
            throw new SheetDisabledException('login.error.sheetDisabled');
        }
    }

    /**
     * @param User  $user
     * @param Event $event
     */
    private function checkUserType(User $user, Event $event)
    {
        $typeFlashBag = $this->session->getFlashBag()->get('register_type');
        $typeId       = array_shift($typeFlashBag);

        $type = $this->typeRepository->getById($typeId);

        if (null !== $type) {
            $this->typeResolver->resolve($user, $event, $type);
        }
    }
}
