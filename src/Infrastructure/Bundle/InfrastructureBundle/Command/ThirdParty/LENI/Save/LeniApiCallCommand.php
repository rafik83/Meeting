<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Save;

use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\LeniApiCallHandler;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeniApiCallCommand extends Command
{
    const NAME = 'vimeet:api:leni-save-call';
    const EXTRA_DATA_ID = 'extraDataId';

    /** @var LeniApiCallHandler */
    private $leniApiCallHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param LeniApiCallHandler           $leniApiCallHandler
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(
        LeniApiCallHandler $leniApiCallHandler,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        parent::__construct(self::NAME);

        $this->leniApiCallHandler = $leniApiCallHandler;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Call the LENI API with the data of one user')
            ->addArgument(self::EXTRA_DATA_ID, InputArgument::REQUIRED, 'ExtraData id of a user');
    }

    /**
     * {@inheritdoc}
     *
     * @throws LeniException
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extraDataId = $input->getArgument(self::EXTRA_DATA_ID);
        $extraData = $this->extraDataRepository->getById($extraDataId);

        if (null === $extraData) {
            throw new \InvalidArgumentException(sprintf('ExtraData not found for id %s', $extraDataId));
        }

        try {
            $this->leniApiCallHandler->handle(new LeniApiCall($extraData));
        } catch (\Exception $leniException) {
            throw new LeniException($leniException->getMessage(), $leniException->getCode(), $leniException);
        }
    }
}
