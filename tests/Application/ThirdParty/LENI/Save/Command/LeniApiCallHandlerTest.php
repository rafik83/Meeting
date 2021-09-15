<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Command;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\UserExtraData\UserExtraDataFingerprintManager;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\LeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomDataHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandleWithoutUserId()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $data = [
            'Cab2' => '24601',
            'CleExterne' => 24601,
            'Societe' => 'vi-meet',
            'CategorieIndividuEvt' => '',
            'ZL_SOUSCATEGORIE' => '327',
            'Civilite' => 'MME',
            'Prenom' => 'Monica',
            'Nom' => 'Chanterelle',
            'Fonction' => 'Gérant',
            'Email' => '24601@example.net',
            'TelephoneMobile' => '+33761426319',
            'TelephoneFixe' => '+33761426319',
            'ZL_RDVNONORGANISES' => '',
            'Pays' => 'ZA',
            'Inscrit' => 'Inscrit',
            'Langue' => 'fr',
            'ZL_ACTIF' => 'ACTI',
            'ZL_ETATDEPAIEMENT' => 'PA',
            'ZL_IDPRODUITPARTICIPANT' => NULL,
        ];

        $response = [
            'Info' => [
                'Id' => [
                    'Value' => '294b141a-2329',
                ],
            ],
        ];

        $extraData = new ExtraData(
            $user->reveal(),
            $event->reveal(),
            'leni_fingerprint_pending',
            'a:19:{s:4:"Cab2";s:5:"24601";s:10:"CleExterne";i:24601;s:7:"Societe";s:7:"vi-meet";s:20:"CategorieIndividuEvt";s:0:"";s:16:"ZL_SOUSCATEGORIE";s:3:"327";s:8:"Civilite";s:3:"MME";s:6:"Prenom";s:6:"Monica";s:3:"Nom";s:11:"Chanterelle";s:8:"Fonction";s:7:"Gérant";s:5:"Email";s:17:"24601@example.net";s:15:"TelephoneMobile";s:12:"+33761426319";s:13:"TelephoneFixe";s:12:"+33761426319";s:18:"ZL_RDVNONORGANISES";s:0:"";s:4:"Pays";s:2:"ZA";s:7:"Inscrit";s:7:"Inscrit";s:6:"Langue";s:2:"fr";s:8:"ZL_ACTIF";s:4:"ACTI";s:17:"ZL_ETATDEPAIEMENT";s:2:"PA";s:23:"ZL_IDPRODUITPARTICIPANT";N;}',
            new \DateTime()
        );

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->save($event->reveal(), $data)->shouldBeCalled()->willReturn($response);

        $userExtraDataFingerprintManager = $this->prophesize(UserExtraDataFingerprintManager::class);
        $userExtraDataFingerprintManager
            ->addOrUpdateFingerprint(
                $event->reveal(),
                $user->reveal(),
                serialize(array_merge($data, ['Id' => '294b141a-2329']))
            )
            ->shouldBeCalled()
        ;

        $dateTime = new \DateTime();
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->remove($extraData)->shouldBeCalled();
        $extraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), Type::LENI_USER_ID, '294b141a-2329', $dateTime))
            ->shouldBeCalled()
        ;

        $getCustomDataHandler = $this->prophesize(GetCustomDataHandler::class);
        $getCustomDataHandler
            ->handle(new GetCustomData($event->reveal(), $user->reveal(), $data))
            ->shouldBeCalled()
            ->willReturn($data)
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $extraDataRepository->reveal(),
            $leniApi->reveal(),
            $userExtraDataFingerprintManager->reveal(),
            $getCustomDataHandler->reveal(),
            $dateTime
        );

        $leniApiCallHandler->handle(new LeniApiCall($extraData));
    }

    public function testHandleWithUserId()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $data = [
            'Cab2' => '24601',
            'CleExterne' => 24601,
            'Societe' => 'vi-meet',
            'CategorieIndividuEvt' => '',
            'ZL_SOUSCATEGORIE' => '327',
            'Civilite' => 'MME',
            'Prenom' => 'Monica',
            'Nom' => 'Chanterelle',
            'Fonction' => 'Gérant',
            'Email' => '24601@example.net',
            'TelephoneMobile' => '+33761426319',
            'TelephoneFixe' => '+33761426319',
            'ZL_RDVNONORGANISES' => '',
            'Pays' => 'ZA',
            'Inscrit' => 'Inscrit',
            'Langue' => 'fr',
            'ZL_ACTIF' => 'ACTI',
            'ZL_ETATDEPAIEMENT' => 'PA',
            'ZL_IDPRODUITPARTICIPANT' => NULL,
            'Id' => '294b141a-2329',
        ];

        $response = [
            'Info' => [
                'Id' => [
                    'Value' => '294b141a-2329',
                ],
            ],
        ];

        $extraData = new ExtraData(
            $user->reveal(),
            $event->reveal(),
            'leni_fingerprint_pending',
            'a:20:{s:4:"Cab2";s:5:"24601";s:10:"CleExterne";i:24601;s:7:"Societe";s:7:"vi-meet";s:20:"CategorieIndividuEvt";s:0:"";s:16:"ZL_SOUSCATEGORIE";s:3:"327";s:8:"Civilite";s:3:"MME";s:6:"Prenom";s:6:"Monica";s:3:"Nom";s:11:"Chanterelle";s:8:"Fonction";s:7:"Gérant";s:5:"Email";s:17:"24601@example.net";s:15:"TelephoneMobile";s:12:"+33761426319";s:13:"TelephoneFixe";s:12:"+33761426319";s:18:"ZL_RDVNONORGANISES";s:0:"";s:4:"Pays";s:2:"ZA";s:7:"Inscrit";s:7:"Inscrit";s:6:"Langue";s:2:"fr";s:8:"ZL_ACTIF";s:4:"ACTI";s:17:"ZL_ETATDEPAIEMENT";s:2:"PA";s:23:"ZL_IDPRODUITPARTICIPANT";N;s:2:"Id";s:13:"294b141a-2329";}',
            new \DateTime()
        );

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->save($event->reveal(), $data)->shouldBeCalled()->willReturn($response);

        $userExtraDataFingerprintManager = $this->prophesize(UserExtraDataFingerprintManager::class);
        $userExtraDataFingerprintManager
            ->addOrUpdateFingerprint(
                $event->reveal(),
                $user->reveal(),
                serialize($data)
            )
            ->shouldBeCalled()
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->remove($extraData)->shouldBeCalled();

        $dateTime = new \DateTime();

        $getCustomDataHandler = $this->prophesize(GetCustomDataHandler::class);
        $getCustomDataHandler
            ->handle(new GetCustomData($event->reveal(), $user->reveal(), $data))
            ->shouldBeCalled()
            ->willReturn($data)
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $extraDataRepository->reveal(),
            $leniApi->reveal(),
            $userExtraDataFingerprintManager->reveal(),
            $getCustomDataHandler->reveal(),
            $dateTime
        );

        $leniApiCallHandler->handle(new LeniApiCall($extraData));
    }

    public function testMissingIdException()
    {
        $this->expectException(MissingIdException::class);

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $response = ['Info' => []];

        $extraData = new ExtraData(
            $user->reveal(),
            $event->reveal(),
            'leni_fingerprint_pending',
            'a:1:{s:4:"Cab2";s:5:"24601";}',
            new \DateTime()
        );

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi
            ->save($event->reveal(), ['Cab2' => '24601', 'EvenementOrigin' => 'API'])
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $userExtraDataFingerprintManager = $this->prophesize(UserExtraDataFingerprintManager::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $getCustomDataHandler = $this->prophesize(GetCustomDataHandler::class);
        $getCustomDataHandler
            ->handle(new GetCustomData($event->reveal(), $user->reveal(), ['Cab2' => '24601']))
            ->shouldBeCalled()
            ->willReturn(['Cab2' => '24601', 'EvenementOrigin' => 'API'])
        ;

        $dateTime = new \DateTime();
        $leniApiCallHandler = new LeniApiCallHandler(
            $extraDataRepository->reveal(),
            $leniApi->reveal(),
            $userExtraDataFingerprintManager->reveal(),
            $getCustomDataHandler->reveal(),
            $dateTime
        );

        $leniApiCallHandler->handle(new LeniApiCall($extraData));
    }

    public function testWarningApiCallException()
    {
        $this->expectException(WarningApiCallException::class);

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $data = [
            'Cab2' => '24601',
            'CleExterne' => 24601,
            'Societe' => 'vi-meet',
            'CategorieIndividuEvt' => '',
            'ZL_SOUSCATEGORIE' => '327',
            'Civilite' => 'MME',
            'Prenom' => 'Monica',
            'Nom' => 'Chanterelle',
            'Fonction' => 'Gérant',
            'Email' => '24601@example.net',
            'TelephoneMobile' => '+33761426319',
            'TelephoneFixe' => '+33761426319',
            'ZL_RDVNONORGANISES' => '',
            'Pays' => 'ZA',
            'Inscrit' => 'Inscrit',
            'Langue' => 'fr',
            'ZL_ACTIF' => 'ACTI',
            'ZL_ETATDEPAIEMENT' => 'PA',
            'ZL_IDPRODUITPARTICIPANT' => NULL,
        ];

        $response = [
            'Info' => [
                'Id' => [
                    'Value' => '294b141a-2329',
                ],
            ],
            'HasWarning' => true, // throw a WarningApiCallException
        ];

        $extraData = new ExtraData(
            $user->reveal(),
            $event->reveal(),
            'leni_fingerprint_pending',
            'a:19:{s:4:"Cab2";s:5:"24601";s:10:"CleExterne";i:24601;s:7:"Societe";s:7:"vi-meet";s:20:"CategorieIndividuEvt";s:0:"";s:16:"ZL_SOUSCATEGORIE";s:3:"327";s:8:"Civilite";s:3:"MME";s:6:"Prenom";s:6:"Monica";s:3:"Nom";s:11:"Chanterelle";s:8:"Fonction";s:7:"Gérant";s:5:"Email";s:17:"24601@example.net";s:15:"TelephoneMobile";s:12:"+33761426319";s:13:"TelephoneFixe";s:12:"+33761426319";s:18:"ZL_RDVNONORGANISES";s:0:"";s:4:"Pays";s:2:"ZA";s:7:"Inscrit";s:7:"Inscrit";s:6:"Langue";s:2:"fr";s:8:"ZL_ACTIF";s:4:"ACTI";s:17:"ZL_ETATDEPAIEMENT";s:2:"PA";s:23:"ZL_IDPRODUITPARTICIPANT";N;}',
            new \DateTime()
        );

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->save($event->reveal(), $data)->shouldBeCalled()->willReturn($response);

        $userExtraDataFingerprintManager = $this->prophesize(UserExtraDataFingerprintManager::class);
        $userExtraDataFingerprintManager
            ->addOrUpdateFingerprint(
                $event->reveal(),
                $user->reveal(),
                serialize(array_merge($data, ['Id' => '294b141a-2329']))
            )
            ->shouldBeCalled()
        ;

        $dateTime = new \DateTime();
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->remove($extraData)->shouldBeCalled();
        $extraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), Type::LENI_USER_ID, '294b141a-2329', $dateTime))
            ->shouldBeCalled()
        ;

        $getCustomDataHandler = $this->prophesize(GetCustomDataHandler::class);
        $getCustomDataHandler
            ->handle(new GetCustomData($event->reveal(), $user->reveal(), $data))
            ->shouldBeCalled()
            ->willReturn($data)
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $extraDataRepository->reveal(),
            $leniApi->reveal(),
            $userExtraDataFingerprintManager->reveal(),
            $getCustomDataHandler->reveal(),
            $dateTime
        );

        $leniApiCallHandler->handle(new LeniApiCall($extraData));
    }
}
