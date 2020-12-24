<?php

namespace Proximum\Vimeet\Tests\Application\Components\User\Event\Denormalizer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\User\Event\Denormalizer\AuthenticationTokenDenormalizer;
use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationTokenDenormalizerTest extends TestCase
{
    private $event;
    private $validator;
    private $authenticationTokenDenormalizer;

    public function setUp()
    {
        $this->validator = $this->prophesize(ValidatorInterface::class);

        $this->event = EventFactory::createEvent();
        $this->authenticationTokenDenormalizer = new AuthenticationTokenDenormalizer($this->validator->reveal());
    }

    public function testDenormalize()
    {
        $data = [
            [
                'email' => '38016@example.net',
                'token' => 'AABBCCDDEE',
                'expiration' => '2020-01-01',
            ],
            [
                'email' => '46820@example.net',
                'token' => 'FFGGHHIIKK',
                'expiration' => '',
            ]
        ];

        $constraintViolationList = $this->prophesize(ConstraintViolationList::class);
        $constraintViolationList->count()
            ->shouldBeCalled()
            ->willReturn(0);

        $this->validator->validate('2020-01-01', [new Date()])
            ->shouldBeCalled()
            ->willReturn($constraintViolationList->reveal());

        $authenticationTokenImport1 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $this->event,
                '38016@example.net',
                'AABBCCDDEE',
                new \DateTime('2020-01-01')
            )
        );

        $authenticationTokenImport2 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $this->event,
                '46820@example.net',
                'FFGGHHIIKK'
            )
        );

        $expectedResult = [
            $authenticationTokenImport1,
            $authenticationTokenImport2,
        ];

        $result = $this
            ->authenticationTokenDenormalizer
            ->denormalize(
                $data,
                AuthenticationTokenImport::class,
                'csv',
                [
                    'csv_delimiter' => ';',
                    'event' => $this->event,
                ]
            );

        $this->assertEquals($expectedResult, $result);
    }

    public function testDenormalizeWithBadHeaders()
    {
        $data = [
            [
                'email1' => '',
                'token1' => '',
                'expiration1' => '',
            ],
            [
                'token' => '',
                'email' => '',
            ]
        ];

        $authenticationTokenImport1 = new AuthenticationTokenImport(null);
        $authenticationTokenImport1->addError('validators.authentication_token.csv.invalid_keys');

        $authenticationTokenImport2 = new AuthenticationTokenImport(null);
        $authenticationTokenImport2->addError('validators.authentication_token.csv.invalid_keys');

        $expectedResult = [
            $authenticationTokenImport1,
            $authenticationTokenImport2
        ];

        $result = $this
            ->authenticationTokenDenormalizer
            ->denormalize(
                $data,
                AuthenticationTokenImport::class,
                'csv',
                [
                    'csv_delimiter' => ';',
                    'event' => $this->event,
                ]
            );

        $this->assertEquals($expectedResult, $result);
    }

    public function testDenormalizeWithBadExpirationDate()
    {
        $data = [
            [
                'email' => 'aa@aa.fr',
                'token' => 'aa',
                'expiration' => 'azerty'
            ]
        ];

        $authenticationTokenImport = new AuthenticationTokenImport(null);
        $authenticationTokenImport->addError('validators.authentication_token.csv.invalid_expiration_date');

        $constraintViolationList = $this->prophesize(ConstraintViolationList::class);

        $this->validator->validate('azerty', [new Date()])
            ->shouldBeCalled()
            ->willReturn($constraintViolationList);

        $constraintViolationList->count()
            ->shouldBeCalled()
            ->willReturn(1);

        $expectedResult = [
            $authenticationTokenImport,
        ];

        $result = $this
            ->authenticationTokenDenormalizer
            ->denormalize(
                $data,
                AuthenticationTokenImport::class,
                'csv',
                [
                    'csv_delimiter' => ';',
                    'event' => $this->event,
                ]
            );

        $this->assertEquals($expectedResult, $result);
    }
}
