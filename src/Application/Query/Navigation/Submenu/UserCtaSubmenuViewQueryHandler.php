<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UserCtaSubmenuViewQueryHandler
{

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;


    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        SheetRepositoryInterface $sheetRepository
    )
    {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(UserCtaSubmenuViewQuery $query): ?SubmenuButtonView
    {
        $customUserIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $query->event,
            Type::TYPE_CUSTOM_BUTTON
        );

        if (null === $customUserIdExtraParameter) {
            return null;
        }

        $parameters = json_decode($customUserIdExtraParameter->getValue(), true);

        if (isset($parameters['concerned_type_ids'])) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);
            $found = false;
            foreach ($sheets as $sheet) {
                $found = in_array($sheet->getType()->getId(), $parameters['concerned_type_ids'], false);
                if ($found) {
                    break;
                }
            }

            if (!$found) {
                return null;
            }
        }

        $placeholders = ['%userId%', '%userEmail%'];
        $values = [urlencode($query->user->getId()), urlencode($query->user->getEmail())];
        $link = str_replace($placeholders, $values, $parameters['link']);

        return new SubmenuButtonView(
            Category::CUSTOM_BUTTON_ICON,
            $parameters['button-label'][$query->locale],
            $link,
            false,
            null,
            false,
            ['target' => '_blank']
        );
    }
}
