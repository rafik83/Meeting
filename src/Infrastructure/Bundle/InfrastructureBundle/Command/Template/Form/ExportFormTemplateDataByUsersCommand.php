<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Template\Form;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateDataByUsers;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportFormTemplateDataByUsersCommand extends Command
{
    public const NAME = 'vimeet:export:form-template-data-by-users';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        FormTemplateRepositoryInterface $formTemplateRepository,
        AdminRepositoryInterface $adminRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->formTemplateRepository = $formTemplateRepository;
        $this->adminRepository = $adminRepository;
        $this->userRepository = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export Form Template data by users')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event ID')
            ->addArgument('formTemplateId', InputArgument::REQUIRED, 'FormTemplate ID')
            ->addArgument('extraDataId', InputArgument::REQUIRED, 'ExtraData ID')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin ID')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale of the export')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $extraData = $this->extraDataRepository->findById($input->getArgument('extraDataId'));

        if (!$extraData instanceof Event\ExtraData) {
            throw new \InvalidArgumentException('Extra data not found.');
        }

        $formTemplate = $this->formTemplateRepository->getById($input->getArgument('formTemplateId'));

        if (!$formTemplate instanceof FormTemplate) {
            throw new \InvalidArgumentException('Form Template not found.');
        }

        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $userIds = explode(',', $extraData->getValue());
        $users = $this->userRepository->findByIds($userIds);
        $locale = $input->getArgument('locale');

        $this->commandBus->handle(new ExportFormTemplateDataByUsers($event, $formTemplate, $users, $admin, $locale));
    }
}
