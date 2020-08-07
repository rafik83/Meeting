<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;

class ParticipantManager
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SheetManager */
    private $sheetManager;

    /** @var UserManager */
    private $userManager;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetManager $sheetManager,
        UserManager $userManager,
        CommandBusInterface $commandBus
    ) {
        $this->participantRepository = $participantRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetManager = $sheetManager;
        $this->userManager = $userManager;
        $this->commandBus = $commandBus;
    }

    public function create(Event $event, Sheet $sheet = null, User $user = null, bool $isImported = false): Participant
    {
        if (null === $sheet) {
            $sheet = $this->sheetManager->create($event);
        }

        if (null === $user) {
            $user = $this->userManager->create();
        }

        if ($isImported) {
            $participant = ParticipantFactory::createImported($sheet, $user);
        } else {
            $participant = ParticipantFactory::create($sheet, $user);
        }

        $participant->setData([]);
        $this->participantRepository->add($participant);
        $this->commandBus->handle(new Update($participant->getUser(), $participant->getEvent()));

        return $participant;
    }

    public function register(Participant $participant, string $firstname, string $lastname): void
    {
        $participant->getSheet()->setRegistrationData([
            // Chiffre d'affaires
            'adc97e8d' => ['items' => ['turnover2']],
            // Nom (Société / Organisme)
            '3ad4b72f' => ['text' => $participant->getSheet()->getTitle()],
        ]);

        $object = $participant->getData();
        $object['0aea62b3'] = ['text' => $firstname];
        $object['0aea62b4'] = ['text' => $lastname];
        $object['adc97e8d'] = ['nomenclature' => 'turnover2'];

        $participant->setData($object);

        $participant->setActive(true);

        $this->participantRepository->set($participant);
    }

    public function setVisioEnabled(Participant $participant): void
    {
        $this->extraDataRepository->add(
            new ExtraData(
                $participant->getUser(),
                $participant->getEvent(),
                Type::IS_PARTICIPANT_VISIO,
                true,
                new \DateTime()
            )
        );

        $this->participantRepository->set($participant);
    }
}
