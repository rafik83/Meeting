<?php


namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Component\Translation\Translator;

class UserCtaSubmenuViewQueryHandlerTest extends TestCase
{
    public function testCustomButton()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1337);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(137);

        $translator = $this->prophesize(Translator::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository->findByEventAndType($event->reveal(), Type::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn(true);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $translator->reveal(),
            $extraParameterRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $expectedCustomButtonSubmenuButtonView = new SubmenuButtonView(
        Category::CUSTOM_BUTTON_ICON,
            'mon bouton',
            'https://www.google.fr',
            false,
            null,
            false,
            ['target' => '_blank']
    );

        $this->assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }
}
