<?php

namespace Proximum\Vimeet\Tests\Application\Components\User\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Components\User\Event\AuthenticationTokenImportParser;
use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\Validator\ConstraintViolationList;

class AuthenticationTokenImportParserTest extends TestCase
{
    public function testParse()
    {
        $event = EventFactory::createEvent();
        $importedFile = $this->prophesize(File::class);
        $validator = $this->prophesize(ValidatorAdapter::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $serializer = $this->prophesize(SerializerAdapterInterface::class);

        // Valid import
        $authenticationTokenImport1 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                '38016@example.net',
                'AABBCCDDEE',
                new \DateTime('2020-01-01')
            )
        );
        $userRepository->emailExists('38016@example.net')
            ->shouldBeCalled()
            ->willReturn(true);
        $constraintViolationList1 = $this->prophesize(ConstraintViolationList::class);
        $constraintViolationList1->count()
            ->shouldBeCalled()
            ->willReturn(0);
        $validator->validate('38016@example.net', ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn($constraintViolationList1->reveal());

        // Bad email format
        $authenticationTokenImport2 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                'azerty',
                'FFGGHHIIKK'
            )
        );
        $constraintViolationList2 = $this->prophesize(ConstraintViolationList::class);
        $constraintViolationList2->count()
            ->shouldBeCalled()
            ->willReturn(1);
        $validator->validate('azerty', ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn($constraintViolationList2->reveal());

        // Already imported email
        $authenticationTokenImport3 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                '38016@example.net',
                'aa'
            )
        );

        // Unknown address
        $authenticationTokenImport4 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                'aaa@example.net',
                'aa'
            )
        );
        $constraintViolationList3 = $this->prophesize(ConstraintViolationList::class);
        $constraintViolationList3->count()
            ->shouldBeCalled()
            ->willReturn(0);
        $validator->validate('aaa@example.net', ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn($constraintViolationList3->reveal());
        $userRepository->emailExists('aaa@example.net')
            ->shouldBeCalled()
            ->willReturn(false);

        $denormalizedData = [
            $authenticationTokenImport1,
            $authenticationTokenImport2,
            $authenticationTokenImport3,
            $authenticationTokenImport4,
        ];

        $serializer->deserialize(
            Argument::any(),
            AuthenticationTokenImport::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'event' => $event,
            ]
        )
        ->shouldBeCalled()
        ->willReturn($denormalizedData);

        $expectedAuthenticationTokenImport2 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                'azerty',
                'FFGGHHIIKK'
            )
        );
        $expectedAuthenticationTokenImport2->addError('validators.authentication_token.csv.email.error');

        $expectedAuthenticationTokenImport3 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                '38016@example.net',
                'aa'
            )
        );
        $expectedAuthenticationTokenImport3->addError('validators.authentication_token.csv.email_already_imported');

        $expectedAuthenticationTokenImport4 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                'aaa@example.net',
                'aa'
            )
        );
        $expectedAuthenticationTokenImport4->addError('validators.authentication_token.csv.unknown_email');

        $expectedResult = [
            $authenticationTokenImport1,
            $expectedAuthenticationTokenImport2,
            $expectedAuthenticationTokenImport3,
            $expectedAuthenticationTokenImport4,
        ];

        $authenticationTokenImportParser = new AuthenticationTokenImportParser(
            $serializer->reveal(),
            $userRepository->reveal(),
            $validator->reveal(),
            __DIR__.'/import_token.csv'
        );
        $result = $authenticationTokenImportParser->parse($event, $importedFile->reveal());

        $this->assertEquals($result, $expectedResult);
    }
}
