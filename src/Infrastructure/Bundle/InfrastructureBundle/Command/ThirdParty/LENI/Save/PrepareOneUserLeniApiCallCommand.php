<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Save;

use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareUserDataForApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareUserDataForApiCallHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareOneUserLeniApiCallCommand extends Command
{
    const NAME = 'vimeet:api:leni-export-data-of-one-user';

    /** @var PrepareUserDataForApiCallHandler */
    private $prepareUserDataForApiCallHandler;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param PrepareUserDataForApiCallHandler $prepareUserDataForApiCallHandler
     * @param EventRepositoryInterface         $eventRepository
     * @param UserRepositoryInterface          $userRepository
     * @param ExtraDataRepositoryInterface     $extraDataRepository
     */
    public function __construct(
        PrepareUserDataForApiCallHandler $prepareUserDataForApiCallHandler,
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        parent::__construct(self::NAME);

        $this->prepareUserDataForApiCallHandler = $prepareUserDataForApiCallHandler;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send to the LENI API the data of the given user for the given event')
            ->addArgument('user', InputArgument::REQUIRED, 'Id of a user')
            ->addArgument('event', InputArgument::REQUIRED, 'Id of an event')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('event'));
        $user = $this->userRepository->findOneById($input->getArgument('user'));

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User not found.');
        }

        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            Type::LENI_FINGERPRINT,
            $user
        );
        $this->prepareUserDataForApiCallHandler->handle(new PrepareUserDataForApiCall(
            $event,
            $user,
            $extraData
        ));
    }
}
