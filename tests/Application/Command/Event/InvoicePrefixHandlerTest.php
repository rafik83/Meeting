<?php

namespace Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\InvoicePrefix\Create;
use Proximum\Vimeet\Application\Command\InvoicePrefix\CreateHandler;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;

class InvoicePrefixHandlerTest extends TestCase
{
    public function testCreateHandler()
    {
        $prefix         = new Prefix('Vimeet', 'Vi');
        $create         = new Create();
        $create->title  = 'Vimeet';
        $create->prefix = 'Vi';

        // Mock
        $prefixRepository = $this->prophesize(PrefixRepositoryInterface::class);

        $prefixRepository->add($prefix)->shouldBeCalled();

        $handler = new CreateHandler($prefixRepository->reveal());
        $handler->handle($create);
    }
}
