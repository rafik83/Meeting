<?php

namespace Proximum\Vimeet\Tests\Domain\Invoice\Numero;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Invoice\CanNotExplodeNotValidNumeroInvoiceException;
use Proximum\Vimeet\Domain\Invoice\Numero\Exploder;
use Proximum\Vimeet\Domain\Invoice\Numero\InvoiceNumeroView;

class ExploderTest extends TestCase
{
    public function testExplodeCorrect()
    {
        $numero1   = 'Vi2017-0234';
        $numero2   = 'Vi-meet-is-a-Long-prEfix-2017-0234';
        $numero3   = 'Vi2018-00000000234';
        $numero4   = 'Other2017-0234';
        $numero5   = '2017-0234';
        $numero6   = '2017-0001';
        $numero7   = '2017-1234567890';
        $numero8   = 'ViMeet2017-1234567890';
        $numero9   = '20172017-0023';
        $expected1 = new InvoiceNumeroView('Vi', 2017, 234);
        $expected2 = new InvoiceNumeroView('Vi-meet-is-a-Long-prEfix-', 2017, 234);
        $expected3 = new InvoiceNumeroView('Vi', 2018, 234);
        $expected4 = new InvoiceNumeroView('Other', 2017, 234);
        $expected5 = new InvoiceNumeroView('', 2017, 234);
        $expected6 = new InvoiceNumeroView('', 2017, 1);
        $expected7 = new InvoiceNumeroView('', 2017, 1234567890);
        $expected8 = new InvoiceNumeroView('ViMeet', 2017, 1234567890);
        $expected9 = new InvoiceNumeroView('2017', 2017, 23);

        $result1 = Exploder::explode($numero1);
        $result2 = Exploder::explode($numero2);
        $result3 = Exploder::explode($numero3);
        $result4 = Exploder::explode($numero4);
        $result5 = Exploder::explode($numero5);
        $result6 = Exploder::explode($numero6);
        $result7 = Exploder::explode($numero7);
        $result8 = Exploder::explode($numero8);
        $result9 = Exploder::explode($numero9);

        $this->assertEquals($expected1, $result1);
        $this->assertEquals($expected2, $result2);
        $this->assertEquals($expected3, $result3);
        $this->assertEquals($expected4, $result4);
        $this->assertEquals($expected5, $result5);
        $this->assertEquals($expected6, $result6);
        $this->assertEquals($expected7, $result7);
        $this->assertEquals($expected8, $result8);
        $this->assertEquals($expected9, $result9);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalid()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = 'fail';
        Exploder::explode($numero);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalidWithoutIncrement()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = 'fail2017';
        Exploder::explode($numero);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalidYear()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = 'fail-0001';
        Exploder::explode($numero);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalidIncrement()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = 'fail2017-test';
        Exploder::explode($numero);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalidFakeNumber()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = '2017Vi-test';
        Exploder::explode($numero);
    }

    /**
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     */
    public function testExplodeInvalidYearNumber()
    {
        $this->expectException(CanNotExplodeNotValidNumeroInvoiceException::class);

        $numero = '201720Vi-2017';
        Exploder::explode($numero);
    }
}
