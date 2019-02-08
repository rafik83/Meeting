<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet\ShowUploadedFileAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class ShowUploadedFileActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $templateData;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ShowUploadedFileAction */
    private $showUploadedFileAction;

    /** @var Request */
    private $request;

    /** @var EventDomain */
    private $eventDomain;

    /** @var ObjectProphecy */
    private $ruleRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var string */
    private $sharedUploadedFiles;

    /** @var ObjectProphecy */
    private $user;

    /** @var UserDomain */
    private $userDomain;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getId()->willReturn(1337);

        $this->request = new Request();
        $this->request->setLocale('fr');

        $this->userDomain = new UserDomain($this->user->reveal());
        $this->eventDomain = new EventDomain($this->event->reveal());

        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);

        $this->templateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        $this->ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->sharedUploadedFiles = 'tests/Ui/Bundle/EventBundle/Controller/Sheet';
        $this->requestRepository  = $this->prophesize(RequestRepositoryInterface::class);

        $canSeeSheet = new CanSeeSheet($this->ruleRepository->reveal(), $this->requestRepository->reveal());

        $this->showUploadedFileAction = new ShowUploadedFileAction(
            $this->authorizationChecker->reveal(),
            $this->ruleRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->sharedUploadedFiles,
            $canSeeSheet
        );
    }

    public function test_show_uploaded_file()
    {
        $file = '/Fixtures/vimeet.jpg';

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $this->sheet->isInInternalCatalog()->willReturn(true);
        $this->sheet->getType()->willReturn($type1->reveal());

        $sheetToDisplay = $this->prophesize(Sheet::class);
        $sheetToDisplay->getId()->willReturn(42);
        $sheetToDisplay->isInInternalCatalog()->willReturn(true);
        $sheetToDisplay->getEvent()->willReturn($this->event->reveal());
        $sheetToDisplay->getType()->willReturn($type2->reveal());
        $this->sheetRepository->getSheetById(42)->shouldBeCalled()->willReturn($sheetToDisplay->reveal());

        $this->templateDataFactory
            ->createFromSheet($sheetToDisplay->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($this->sheet->reveal(), $sheetToDisplay->reveal())
            ->shouldBeCalled()
            ->willReturn([new Rule($this->event->reveal(), $type1->reveal(), $type2->reveal(), [])])
        ;

        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);

        $multiUploadObject = $this->prophesize(MultiUploadCollectionObject::class);
        $multiUploadObject
            ->hasUpload($file)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->templateData
            ->getObject('multi-upload-object-uid')
            ->shouldBeCalled()
            ->willReturn($multiUploadObject->reveal())
        ;

        $this->assertInstanceOf(
            BinaryFileResponse::class,
            ($this->showUploadedFileAction)(
                $this->request,
                $this->eventDomain,
                $this->userDomain,
                $this->sheet->reveal(),
                42,
                'multi-upload-object-uid',
                $file
            )
        );
    }

    public function test_displayed_sheet_and_current_sheet_are_same()
    {
        $file = '/Fixtures/vimeet.jpg';

        $this->sheetRepository->getSheetById(1337)->shouldBeCalled()->willReturn($this->sheet->reveal());

        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);

        $multiUploadObject = $this->prophesize(MultiUploadCollectionObject::class);
        $multiUploadObject
            ->hasUpload($file)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->templateData
            ->getObject('multi-upload-object-uid')
            ->shouldBeCalled()
            ->willReturn($multiUploadObject->reveal())
        ;

        $this->assertInstanceOf(
            BinaryFileResponse::class,
            ($this->showUploadedFileAction)(
                $this->request,
                $this->eventDomain,
                $this->userDomain,
                $this->sheet->reveal(),
                1337,
                'multi-upload-object-uid',
                $file
            )
        );
    }
}
