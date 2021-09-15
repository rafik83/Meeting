<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class AddHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var TypeResolver */
    private $typeResolver;

    /** @var Synchronizer */
    private $accountSynchronizer;

    /** @var UpdateParticipantProductQuantityHandler */
    private $updateParticipantProductQuantityHandler;

    /** @var \DateTimeInterface */
    private $date;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        DelayedEventDispatcher $eventDispatcher,
        UpdateParticipantProductQuantityHandler $updateParticipantProductQuantityHandler,
        TypeResolver $typeResolver,
        Synchronizer $accountSynchronizer,
        \DateTimeInterface $date
    ) {
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
        $this->sheetRepository = $sheetRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->typeResolver = $typeResolver;
        $this->accountSynchronizer = $accountSynchronizer;
        $this->updateParticipantProductQuantityHandler = $updateParticipantProductQuantityHandler;
        $this->date = $date;
    }

    /**
     * @param Add $add
     *
     * @throws AlreadyLinkedToASheetOfThisEventException
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     *
     * @return AddResult
     */
    public function handle(Add $add): AddResult
    {
        if (null === $add->email) {
            throw new EmailCanNotBeNullException();
        }

        $add->email = StringHelper::trimSpacesAndNonBreakSpaces($add->email);

        $user = $this->userRepository->findByEmail($add->email);
        $isNewUser = false;

        if (null === $user) {
            $user = new User($add->email, '', '', $add->locale);
            $this->userRepository->add($user);
            $isNewUser = true;
        }

        if ($add->sheet->hasUserParticipant($user)) {
            throw new ParticipantAlreadyExistException('User already linked to this sheet');
        }

        if (!empty($this->sheetRepository->getSheetsByUserAndEventWhereUserIsParticipant(
            $user,
            $add->sheet->getEvent()
        ))) {
            throw new AlreadyLinkedToASheetOfThisEventException('User already linked to a sheet on this event');
        }

        // Create participant
        $participant = $this->createAndFillParticipant($add, $user, $isNewUser);

        // Update cart
        if ($add->needToSelectProduct) {
            $this->updateParticipantProductQuantityHandler->handle(
                new UpdateParticipantProductQuantity($add->sheet, $participant, $add->product->id)
            );
        }

        // Add UserEvent to new user
        $this->typeResolver->resolve($user, $add->sheet->getEvent(), $add->sheet->getType());

        $sheetUpdated = new SheetUpdatedEvent($add->sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdated);
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_ADDED, new ParticipantAddedEvent($participant, $add->adder));

        return new AddResult($participant);
    }

    /**
     * @param Add  $add
     * @param User $user
     * @param bool $isNewUser
     *
     * @return Participant
     */
    protected function createAndFillParticipant(Add $add, User $user, $isNewUser): Participant
    {
        $templateData = $this->templateDataFactory->createRegistrationFromType($add->sheet->getType(), $add->locale);
        $templateData->setTaggedData([
            Tag::PARTICIPANT_FIRSTNAME => $add->firstName,
            Tag::PARTICIPANT_LASTNAME  => $add->lastName,
        ]);

        $participant = new Participant(
            $add->sheet,
            $user,
            $templateData->getData(),
            false,
            $this->date
        );
        $this->participantRepository->add($participant);

        $add->sheet->addParticipant($participant);

        if (true === $isNewUser) {
            $this->accountSynchronizer->set($templateData, $user);
        }

        return $participant;
    }
}
