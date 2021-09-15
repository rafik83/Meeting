<?php

namespace Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\OMZ\PrepareContent;
use Proximum\Vimeet\Application\Command\OMZ\PrepareContentHandler;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQueryHandler;
use Proximum\Vimeet\Application\Serializer\Normalizer\OMZ\OmzUserNormalizer;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;

class PrepareContentHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $user   = UserFactory::create('normalizer@elao.com');
        $sheet  = SheetFactory::create($event);
        $locale = 'fr';

        // Mock
        $userRepository               = $this->prophesize(UserRepositoryInterface::class);
        $sheetRepository              = $this->prophesize(SheetRepositoryInterface::class);
        $groupNameResolver            = $this->prophesize(GroupNameResolver::class);
        $typeNameResolver             = $this->prophesize(TypeNameResolver::class);
        $serializer                   = $this->prophesize(SerializerAdapterInterface::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $userInfoGuesser              = $this->prophesize(UserInfoGuesser::class);

        $userInfoGuesser
            ->getUserInfoFromParticipant($user, $locale, [$sheet])
            ->shouldBeCalled()
            ->willReturn([
                'gender'    => 'Woman',
                'firstName' => 'first Name',
                'lastName'  => 'last Name',
                'position'  => '',
                'phone'     => 'phone',
                'mobile'    => 'mobile',
            ])
        ;

        $expectedPlanning = 'normalizer@elao.com';
        $omzUserView = new OmzUserView(
            '0000133700007',
            'group name',
            null,
            'type name',
            'Woman',
            'first Name',
            'last Name',
            '',
            null,
            'phone',
            'normalizer@elao.com',
            null,
            'mobile',
            $expectedPlanning
        );
        $omzUserListView = new OmzUserListView([$omzUserView]);

        $expectedNormalizedDatas = 'normalizer@elao.com';

        $QRCodeIdentifierQueryHandler = $this->prophesize(QRCodeIdentifierQueryHandler::class);
        $QRCodeIdentifierQueryHandler
            ->handle(new QRCodeIdentifierQuery($event, $user))
            ->shouldBeCalled()
            ->willReturn('0000133700007')
        ;

        $participantPlanningFormatter->preloadPlanningHandlerForEvent($event)->shouldBeCalled();

        $userRepository->findWithEnabledSheetByEvent($event)->shouldBeCalled()->willReturn([$user]);

        $participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated($user, $event, $locale)
            ->shouldBeCalled()
            ->willReturn($expectedPlanning);

        $sheetRepository->getSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([$sheet]);

        $sheets = [$sheet];
        $groupNameResolver->resolve($event, $user, $sheets)->shouldBeCalled()->willReturn('group name');
        $typeNameResolver->resolveWithPreloadedSheets($sheets, 'fr')->shouldBeCalled()->willReturn('type name');

        $serializer->serialize($omzUserListView, 'csv', ['csv_delimiter' => ';', 'charset' => 'Windows-1252'])
            ->shouldBeCalled()
            ->willReturn($expectedNormalizedDatas);

        $command = new PrepareContent($event);
        $handler = new PrepareContentHandler(
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $userInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $QRCodeIdentifierQueryHandler->reveal(),
            $serializer->reveal()
        );

        $resultNormalizedDatas = $handler->handle($command);

        $this->assertEquals($expectedNormalizedDatas, $resultNormalizedDatas);
    }

    public function testNormalize()
    {
        $translator = new Translator('fr');
        $applicationTranslator = new TranslatorAdapter($translator);
        $serializer = new Serializer(
            [
                new OmzUserNormalizer($applicationTranslator),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $omzUserView = new OmzUserView(
            1,
            'group name',
            null,
            'type name',
            'woman',
            'first name',
            'last name',
            'position',
            null,
            'phone',
            'normalizer@elao.com',
            null,
            'mobile',
            "**Dimanche 1 janvier 2017**\n\n- 11:10 - 11:30 : mass title\n- 13:00 - 14:00 : unavailability title\n- 14:15 - 14:45 : assignment mass title\n- 17:00 - 18:00 : happening title\n- 18:30 - 18:45 : spot reference - user sheet title - sheet met title\n\n\n\n\n\n\nunallocated: sheet met 1"
        );
        $omzUserListView = new OmzUserListView([$omzUserView]);

        $result = $serializer->serialize($omzUserListView, 'csv', [
            'csv_delimiter' => ',',
            'charset' => 'Windows-1252',
        ]);

        $expected = "participantId,companyName,description,type,title,firstName,lastName,position,phonePrefix,phoneNumber,email,mobilePhonePrefix,mobilePhone,planning\n1,\"group name\",,\"type name\",woman,\"first name\",\"last name\",position,,phone,normalizer@elao.com,,mobile,\"**Dimanche 1 janvier 2017**\n\n- 11:10 - 11:30 : mass title\n- 13:00 - 14:00 : unavailability title\n- 14:15 - 14:45 : assignment mass title\n- 17:00 - 18:00 : happening title\n- 18:30 - 18:45 : spot reference - user sheet title - sheet met title\n\n\n\n\n\n\nunallocated: sheet met 1\"\n";

        $this->assertEquals($expected, $result);
    }
}
