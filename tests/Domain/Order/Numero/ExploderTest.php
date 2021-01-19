<?php

namespace Proximum\Vimeet\Tests\Domain\Order\Numero;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Order\Numero\CanNotExplodeNotValidNumeroOrderException;
use Proximum\Vimeet\Domain\Order\Numero\Exploder;
use Proximum\Vimeet\Domain\Order\Numero\OrderNumeroView;

class ExploderTest extends TestCase
{
    /**
     * @throws CanNotExplodeNotValidNumeroOrderException
     */
    public function testExplodeException()
    {
        $this->expectException(CanNotExplodeNotValidNumeroOrderException::class);

        Exploder::explode('test');
    }

    public function testExplode()
    {
        $this->assertEquals(new OrderNumeroView(1, 1, 1), Exploder::explode('01-01-01'));
        $this->assertEquals(new OrderNumeroView(12, 1, 1), Exploder::explode('12-01-01'));
        $this->assertEquals(new OrderNumeroView(1, 12, 1), Exploder::explode('01-12-01'));
        $this->assertEquals(new OrderNumeroView(1, 1, 12), Exploder::explode('01-01-12'));
        $this->assertEquals(new OrderNumeroView(12, 12, 12), Exploder::explode('12-12-12'));
    }
}
