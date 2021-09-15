<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign\Assembler;

use Doctrine\ORM\EntityManagerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\CampaignFullView;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Campaign assembler in charge of returning a full campaign view (instance of CampaignFullView) out of a campaign entity.
 * This service is meant to be used by any controller or query handler that must display a full campaign view
 * (assembling a full view is quite a heavy job, hence this service dedicated to it)
 */
class CampaignAssembler
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * Translation keys indexed by their filter key.
     *
     * @var array
     */
    private static $filterLabels = [
        'imported'         => 'form.sheet_filter.children.imported.label',
        'text'             => 'form.sheet_filter.children.text_search.label',
        'validationState'  => 'form.sheet_filter.children.validationState.label',
        'state'            => 'form.sheet_filter.children.state.label',
        'completed'        => 'form.sheet_filter.children.completed.label',
        'type'             => 'form.sheet_filter.children.type.label',
        'category'         => 'form.sheet_filter.children.category.label',
        'follower'         => 'form.sheet_filter.children.follower.label',
        'registeredAt'     => 'form.sheet_filter.children.creation_interval.label',
        'inCatalog'        => 'form.sheet_filter.children.inCatalog.label',
        Constant::NO_ORDER => 'admin.sheet.filter.no_order',
        Constant::HAS_CART => 'admin.sheet.filter.has_cart',
        'boolean_filters'  => 'admin.sheet.filter.template_filters',
        'hasInvoice'       => 'form.sheet_filter.children.hasInvoice.label',
        'hasRemainingToPay'             => 'form.sheet_filter.children.hasRemainingToPay.label',
        'hasHappeningParticipation'     => 'form.sheet_filter.children.hasHappeningParticipation.label',
        'hasNoMeetingRequest'           => 'form.sheet_filter.children.hasNoMeetingRequest.label',
        'hasPendingMeetingPropositions' => 'form.sheet_filter.children.hasPendingMeetingPropositions.label',
        'hasScheduledMeeting'           => 'form.sheet_filter.children.hasScheduledMeeting.label',
    ];

    /**
     * @param TranslatorInterface    $translator
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, SheetInfoGuesser $sheetInfoGuesser, EntityManagerInterface $entityManager)
    {
        $this->translator       = $translator;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->entityManager    = $entityManager;
    }

    /**
     * @param Campaign $campaign
     *
     * @return CampaignFullView
     */
    public function assemble(Campaign $campaign)
    {
        $view = new CampaignFullView($campaign->getId(), $campaign->getTitle(), $campaign->getCreatedAt());

        $event = $campaign->getEvent();
        $locale = $event->getAvailableLocale($this->translator->getLocale());

        $this->addRecipients($view, $campaign);
        $this->addSheets($view, $campaign, $locale);
        $this->addFilters($view, $campaign, $locale);

        return $view;
    }

    /**
     * @param CampaignFullView $view
     * @param Campaign         $campaign
     */
    private function addRecipients(CampaignFullView $view, Campaign $campaign)
    {
        foreach ($campaign->getRecipients() as $recipient) {
            $view->addRecipient($recipient);
        }
    }

    /**
     * @param CampaignFullView $view
     * @param Campaign         $campaign
     * @param string           $locale
     */
    private function addSheets(CampaignFullView $view, Campaign $campaign, $locale)
    {
        foreach ($campaign->getSheets() as $sheet) {
            $sheetName = $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale);
            $view->addSheet(new SheetListView($sheet->getId(), $sheetName, $sheet->getOwner()->getEmail()));
        }
    }

    /**
     * @param CampaignFullView $view
     * @param Campaign         $campaign
     * @param string           $locale
     */
    private function addFilters(CampaignFullView $view, Campaign $campaign, $locale)
    {
        foreach ($campaign->getFilters() as $key => $value) {
            $filterName = $this->getFilterLabel($key);
            $values = (array) $value;
            $label = '';
            foreach ($values as $currentValue) {
                switch ($key) {
                    case 'completed':
                        $currentValue = $currentValue
                            ? $this->translator->trans('event.sheet.completed.complete')
                            : $this->translator->trans('event.sheet.completed.incomplete');
                        break;
                    case 'imported':
                        $currentValue = $this->translator->trans(sprintf('event.sheet.imported.%s.label', $currentValue));
                        break;
                    case 'validationState':
                        $currentValue = $this->translator->trans(sprintf('event.sheet.validationState.%s', $currentValue));
                        break;
                    case 'state':
                        $currentValue = $this->translator->trans(sprintf('event.sheet.state.%s', $currentValue));
                        break;
                    case 'follower':
                        $follower = $this->entityManager->getRepository(Admin::class)->find($currentValue);
                        if ($follower) {
                            $currentValue = $follower->getFullname();
                        } else {
                            $currentValue = '';
                        }
                        break;
                    case 'type':
                        $type = $this->entityManager->getRepository(Type::class)->find($currentValue);
                        $currentValue = $type->getTitle($locale);
                        break;
                    case 'registeredAt':
                        $currentValue = $this->translator->trans(sprintf('admin.sheet.%s', $currentValue));
                        break;
                    case 'boolean_filters':
                        $filter = $this->entityManager->getRepository(BooleanTemplateFilter::class)->findOneBy([
                            'event'       => $campaign->getEvent(),
                            'templateKey' => $currentValue,
                        ]);
                        if ($filter) {
                            $currentValue = $filter->getLabel();
                        } else {
                            $currentValue = '';
                        }
                        break;
                    case 'inCatalog':
                    case Constant::NO_ORDER:
                    case Constant::HAS_CART:
                    case 'hasRemainingToPay':
                    case 'hasHappeningParticipation':
                    case 'hasNoMeetingRequest':
                    case 'hasPendingMeetingPropositions':
                    case 'hasScheduledMeeting':
                    case 'hasInvoice':
                        $currentValue = $currentValue
                            ? $this->translator->trans('boolean.true')
                            : $this->translator->trans('boolean.false');
                        break;
                }

                $label .= ('' !== $label ? ', ' : '') . $currentValue;
            }
            $view->addFilter($filterName, $label);
        }
    }

    /**
     * Get a human-readable translated label for a given filter key.
     *
     * @param string $key
     *
     * @return string
     */
    private function getFilterLabel($key)
    {
        if (isset(self::$filterLabels[$key])) {
            $label = self::$filterLabels[$key];

            return $this->translator->trans($label, [], 0 === strpos($label, 'form.') ? 'forms' : 'messages');
        }

        return $key;
    }
}
