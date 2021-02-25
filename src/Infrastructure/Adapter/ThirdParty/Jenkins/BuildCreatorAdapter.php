<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\Adapter\ExecInterface;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\BuildCreationFailedException;
use Psr\Log\LoggerInterface;

class BuildCreatorAdapter implements BuildCreatorInterface
{
    /** @var string */
    private $jenkinsCommand;

    /** @var string */
    private $jenkinsUser;

    /** @var string */
    private $jenkinsPassword;

    /** @var ExecInterface */
    private $execAdapter;

    private ?LoggerInterface $logger;

    public function __construct(
        ExecInterface $execAdapter,
        string $jenkinsCommand,
        string $jenkinsUser,
        string $jenkinsPassword,
        ?LoggerInterface $logger = null
    ) {
        $this->execAdapter     = $execAdapter;
        $this->jenkinsCommand  = $jenkinsCommand;
        $this->jenkinsUser     = $jenkinsUser;
        $this->jenkinsPassword = $jenkinsPassword;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $buildName, array $arguments = []): string
    {
        $argumentsEncoded = [];

        foreach ($arguments as $key => $value) {
            $argumentsEncoded[] = [
                'name' => $key,
                'value' => $value,
            ];
        }

        $output = [];
        $result = 0;

        $command = strtr(
            $this->jenkinsCommand,
            [
                '%buildName%' => $buildName,
                '%jenkinsUser%' => $this->jenkinsUser,
                '%jenkinsPassword%' => $this->jenkinsPassword,
                '%jenkinsParameters%' => json_encode($argumentsEncoded),
            ]
        );

        $this->execAdapter->exec($command . ' 2>&1', $output, $result);

        if ($result > 0) {
            if ($this->logger) {
                $this->logger->error('[BuildCreationFailed] '.$result);
            }
            throw new BuildCreationFailedException();
        }

        return implode("\n", $output);
    }
}
