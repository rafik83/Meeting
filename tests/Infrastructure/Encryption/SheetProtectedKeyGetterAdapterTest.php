<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ProtectedKeyInterface;
use Proximum\Vimeet\Application\Adapter\SheetProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Encryption\SheetProtectedKeyGetterAdapter;

class SheetProtectedKeyGetterAdapterTest extends TestCase
{
    private $sheet;
    private $sheetExtraDataRepository;
    private $protectedKey;
    private $sheetProtectedKeyPasswordGetter;
    private $dateTime;
    private $sheetProtectedKeyGetterAdapter;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);

        $this->sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->protectedKey = $this->prophesize(ProtectedKeyInterface::class);
        $this->sheetProtectedKeyPasswordGetter = $this->prophesize(
            SheetProtectedKeyPasswordGetterInterface::class
        );
        $this->dateTime = new \DateTime();

        $this->sheetProtectedKeyGetterAdapter = new SheetProtectedKeyGetterAdapter(
            $this->sheetExtraDataRepository->reveal(),
            $this->protectedKey->reveal(),
            $this->sheetProtectedKeyPasswordGetter->reveal(),
            $this->dateTime
        );
    }

    public function testGetProtectedKeyStoredInExtraData()
    {
        $extraData = $this->prophesize(Sheet\ExtraData::class);
        $extraData->getValue()->willReturn('myUserKey');

        $this
            ->sheetExtraDataRepository
            ->getExtraDataForSheet($this->sheet->reveal(), Type::PROTECTED_KEY)
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $result = $this->sheetProtectedKeyGetterAdapter->getProtectedKeyBySheet($this->sheet->reveal());

        $this->assertEquals('myUserKey', $result);
    }

    public function testGenerateAndStoreNewProtectedKey()
    {
        $this
            ->sheetExtraDataRepository
            ->getExtraDataForSheet($this->sheet->reveal(), Type::PROTECTED_KEY)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->sheetProtectedKeyPasswordGetter
            ->getProtectedKeyPasswordBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn('_my_generated_password_')
        ;

        $this
            ->protectedKey
            ->getKeyProtectedByPassword('_my_generated_password_')
            ->shouldBeCalled()
            ->willReturn('__very_secure_protected-KEY')
        ;

        $this
            ->sheetExtraDataRepository
            ->add(
                new Sheet\ExtraData(
                    $this->sheet->reveal(),
                    Type::PROTECTED_KEY,
                    '__very_secure_protected-KEY',
                    $this->dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $result = $this->sheetProtectedKeyGetterAdapter->getProtectedKeyBySheet($this->sheet->reveal());

        $this->assertEquals('__very_secure_protected-KEY', $result);
    }
}
