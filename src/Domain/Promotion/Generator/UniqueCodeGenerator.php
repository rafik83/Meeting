<?php

namespace Proximum\Vimeet\Domain\Promotion\Generator;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;

class UniqueCodeGenerator implements CodeGeneratorInterface
{
    /**
     * @var CodeGeneratorInterface
     */
    private $generator;

    /**
     * @var UniqueCodeChecker
     */
    private $checker;

    /**
     * UniqueCodeGenerator constructor.
     *
     * @param CodeGeneratorInterface $generator
     * @param UniqueCodeChecker      $checker
     */
    public function __construct(CodeGeneratorInterface $generator, UniqueCodeChecker $checker)
    {
        $this->generator = $generator;
        $this->checker   = $checker;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Event $event, ?string $prefix = null): string
    {
        $code   = $this->generator->generate($event, $prefix);
        $suffix = 2;
        $length = strlen($code);

        while ($this->checker->exists($event, $code)) {
            $code = substr($code, 0, $length) . $suffix++;
        }

        return $code;
    }
}
