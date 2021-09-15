<?php

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
    private $sheetRepository;

    /** @var string */
    private $sharedUploadedFiles;

    /** @var ObjectProphecy */
    private $user;

    /** @var UserDomain */
    private $userDomain;

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

        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->sharedUploadedFiles = 'tests/Ui/Bundle/EventBundle/Controller/Sheet';

        $this->showUploadedFileAction = new ShowUploadedFileAction(
            $this->sheetRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->sharedUploadedFiles
        );
    }

    public function test_show_uploaded_file()
    {
        $file = '/Fixtures/vimeet.jpg';

        $sheetToDisplay = $this->prophesize(Sheet::class);
        $sheetToDisplay->getId()->willReturn(42);
        $sheetToDisplay->getEvent()->willReturn($this->event->reveal());
        $this->sheetRepository->getSheetById(42)->shouldBeCalled()->willReturn($sheetToDisplay->reveal());

        $this->templateDataFactory
            ->createFromSheet($sheetToDisplay->reveal(), 'fr')
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
                42,
                'multi-upload-object-uid',
                $file
            )
        );
    }
}
