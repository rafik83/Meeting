<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\MeetingRequest\Remind;
use Proximum\Vimeet\Application\Command\MeetingRequest\ReminderHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendSMSReminderCommand extends Command
{
    const NAME = 'vimeet:sms-reminder:send';

    /** @var ReminderHandler */
    private $reminderHandler;

    /** @var bool */
    private $ddayFeatureEnabled;

    /**
     * @param ReminderHandler $reminderHandler
     * @param bool            $ddayFeatureEnabled
     */
    public function __construct(
        ReminderHandler $reminderHandler,
        bool $ddayFeatureEnabled
    ) {
        parent::__construct(self::NAME);

        $this->reminderHandler    = $reminderHandler;
        $this->ddayFeatureEnabled = $ddayFeatureEnabled;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send SMS reminder to user with count of available pending proposition');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->ddayFeatureEnabled) {
            return;
        }

        $this->reminderHandler->handle(
            new Remind()
        );
    }
}
