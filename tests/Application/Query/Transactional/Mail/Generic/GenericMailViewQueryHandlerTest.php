<?php

namespace Proximum\Vimeet\Tests\Application\Query\Transactional\Mail\Generic;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class GenericMailViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $key = Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED;
        $data = Constant::TRANSACTIONAL_MAIL_LIST[$key];
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $type1->getTitle('fr')->shouldBeCalled()->willReturn('type1');
        $type2->getTitle('fr')->shouldBeCalled()->willReturn('type2');

        $query = new GenericMailViewQuery(
            'fr',
            $key,
            $data,
            [
                $type1->reveal(),
                $type2->reveal(),
            ]
        );

        $translator = $this->prophesize(TranslatorInterface::class);

        $translator->trans($data['subject'], [], 'mail', 'fr')
            ->shouldBeCalled()
            ->willReturn('TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED')
        ;

        $handler = new GenericMailViewQueryHandler($translator->reveal());
        $result = $handler->handle($query);

        $expected = new GenericMailView(
            $key,
            'TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED',
            true,
            [
                'type1',
                'type2',
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
