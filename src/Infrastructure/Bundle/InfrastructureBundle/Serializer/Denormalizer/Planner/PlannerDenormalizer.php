<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Denormalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\Result\MeetingResult;
use Proximum\Vimeet\Application\View\Planner\Result\PlannerResult;
use Proximum\Vimeet\Application\View\Planner\Result\SheetResult;
use Proximum\Vimeet\Application\View\Planner\Result\SlotResult;
use Proximum\Vimeet\Application\View\Planner\Result\SpotResult;
use Proximum\Vimeet\Application\View\Planner\Result\UserResult;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PlannerDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /** @var SheetResult[] */
    private $sheetList = [];

    /** @var UserResult[] */
    private $userList = [];

    /** @var SpotResult[] */
    private $spotList = [];

    /** @var SlotResult[] */
    private $slotList = [];

    /** @var MeetingResult[] */
    private $meetingList = [];

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!is_array($data)
            || !isset($data['meetingList'])
            || !isset($data['sheetList'])
            || !isset($data['dayList'])
            || !isset($data['slotList'])
            || !isset($data['spotList'])
            || !isset($data['userList'])
            || !isset($data['typeList'])
            || !isset($data['typePriorityList'])
            || !isset($data['meetingList']['Meeting'])
            || !isset($data['sheetList']['Sheet'])
            || !isset($data['userList']['User'])
            || !isset($data['spotList']['Spot'])
            || !isset($data['slotList']['Slot'])
        ) {
            throw new \InvalidArgumentException('Missing a required node');
        }

        $this->handleData($data); // Create the list of element from the nodes
        $this->createMeetings($data); // Create the meetings after the creation of the list to get the objects

        if (isset($context[AbstractNormalizer::OBJECT_TO_POPULATE])
            && $context[AbstractNormalizer::OBJECT_TO_POPULATE] instanceof PlannerResult
        ) {
            $plannerResult = $context[AbstractNormalizer::OBJECT_TO_POPULATE];
        } else {
            $plannerResult = new PlannerResult();
        }

        $plannerResult->meetings = $this->meetingList;
        $plannerResult->sheets   = $this->sheetList;
        $plannerResult->users    = $this->userList;
        $plannerResult->slots    = $this->slotList;
        $plannerResult->spots    = $this->spotList;

        return $plannerResult;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return PlannerResult::class === $type && 'xml' === $format;
    }

    private function createMeetings(array $data)
    {
        if (is_array($data['meetingList']['Meeting'])) {
            foreach ($data['meetingList']['Meeting'] as $meeting) {
                $this->createMeeting($meeting);
            }
        }
    }

    /**
     * @param array $data
     */
    private function handleData(array &$data)
    {
        if (is_array($data['meetingList']['Meeting'])) {
            foreach ($data['meetingList']['Meeting'] as $meeting) {
                $this->handleMeeting($meeting);
            }
        }

        if (is_array($data['slotList']['Slot'])) {
            foreach ($data['slotList']['Slot'] as $slot) {
                $this->handleSlot($slot);
            }
        }

        if (is_array($data['spotList']['Spot'])) {
            foreach ($data['spotList']['Spot'] as $spot) {
                $this->handleSpot($spot);
            }
        }

        if (is_array($data['userList']['User'])) {
            if (!$this->isSingle($data['userList']['User'])) {
                foreach ($data['userList']['User'] as $user) {
                    $this->handleUser($user);
                }
            } else {
                $this->handleUser($data['userList']['User']);
            }
        }

        if (is_array($data['sheetList']['Sheet'])) {
            foreach ($data['sheetList']['Sheet'] as $sheet) {
                $this->handleSheet($sheet);
            }
        }
    }

    /**
     * @param array $spot
     *
     * @return null|SpotResult
     */
    private function handleSpot(array &$spot)
    {
        $spotResult = null;

        if (isset($spot['@reference'])) {
            if (isset($this->spotList[$spot['@reference']])) {
                return $this->spotList[$spot['@reference']];
            }

            return $spotResult;
        }

        if (isset($spot['@id']) && isset($spot['id'])) {
            $spotResult = new SpotResult($spot['id']);
            $this->spotList[$spot['@id']] = $spotResult;
        }

        if (isset($spot['sheetList']) && isset($spot['sheetList']['Sheet'])) {
            if ($this->isSingle($spot['sheetList']['Sheet'])) {
                $this->handleSheet($spot['sheetList']['Sheet']);
            } else {
                foreach ($spot['sheetList']['Sheet'] as $sheet) {
                    $this->handleSheet($sheet);
                }
            }
        }

        if (isset($spot['unavailabilityList'])
            && is_array($spot['unavailabilityList'])
            && isset($spot['unavailabilityList']['Slot'])
        ) {
            if ($this->isSingle($spot['unavailabilityList']['Slot'])) {
                $this->handleSlot($spot['unavailabilityList']['Slot']);
            } else {
                foreach ($spot['unavailabilityList']['Slot'] as $slot) {
                    $this->handleSlot($slot);
                }
            }
        }

        return $spotResult;
    }

    /**
     * @param array $user
     *
     * @return null|UserResult
     */
    private function handleUser(array &$user)
    {
        if (isset($user['@reference'])) {
            if (isset($this->userList[$user['@reference']])) {
                return $this->userList[$user['@reference']];
            }

            return null;
        }

        if (isset($user['@id']) && isset($user['id'])) {
            $userResult = new UserResult($user['id']);
            $this->userList[$user['@id']] = $userResult;

            if (isset($user['unavailabilityList'])
                && is_array($user['unavailabilityList'])
                && isset($user['unavailabilityList']['Slot'])
            ) {
                if ($this->isSingle($user['unavailabilityList']['Slot'])) {
                    $this->handleSlot($user['unavailabilityList']['Slot']);
                } else {
                    foreach ($user['unavailabilityList']['Slot'] as $slot) {
                        $this->handleSlot($slot);
                    }
                }
            }

            return $userResult;
        }

        return null;
    }

    /**
     * @param array $slot
     *
     * @return null|SlotResult
     */
    private function handleSlot(array &$slot)
    {
        if (isset($slot['@reference'])) {
            if (isset($this->slotList[$slot['@reference']])) {
                return $this->slotList[$slot['@reference']];
            }

            return null;
        }

        if (isset($slot['@id']) && isset($slot['id'])) {
            $slotResult = new SlotResult($slot['id']);
            $this->slotList[$slot['@id']] = $slotResult;

            return $slotResult;
        }

        return null;
    }

    /**
     * @param array $sheet
     *
     * @return null|SheetResult
     */
    private function handleSheet(array &$sheet)
    {
        if (isset($sheet['@reference'])) {
            if (isset($this->sheetList[$sheet['@reference']])) {
                return $this->sheetList[$sheet['@reference']];
            }

            return null;
        }

        if (isset($sheet['@id']) && isset($sheet['id'])) {
            $sheetResult = new SheetResult($sheet['id']);
            $this->sheetList[$sheet['@id']] = $sheetResult;

            return $sheetResult;
        }

        return null;
    }

    /**
     * This method is used to handle the data potentially initiated in the meeting
     * It is not the method that instanciate the meetingList
     * as the element inside can be not yet added to the other list
     *
     * @param array $meeting
     */
    private function handleMeeting(array &$meeting)
    {
        if (isset($meeting['@reference'])) {
            return;
        }

        if (isset($meeting['sheetList'])
            && is_array($meeting['sheetList'])
            && isset($meeting['sheetList']['Sheet'])
            && is_array($meeting['sheetList']['Sheet'])
        ) {
            if (!$this->isSingle($meeting['sheetList']['Sheet'])) {
                foreach ($meeting['sheetList']['Sheet'] as $sheet) {
                    $this->handleSheet($sheet);
                }
            } else {
                $this->handleSheet($meeting['sheetList']['Sheet']);
            }
        }

        if (isset($meeting['userList'])
            && is_array($meeting['userList'])
            && isset($meeting['userList']['User'])
            && is_array($meeting['userList']['User'])
        ) {
            if (!$this->isSingle($meeting['userList']['User'])) {
                foreach ($meeting['userList']['User'] as $user) {
                    $this->handleUser($user);
                }
            } else {
                $this->handleUser($meeting['userList']['User']);
            }
        }

        if (isset($meeting['spot'])) {
            $this->handleSpot($meeting['spot']);
        }

        if (isset($meeting['lockedSpot'])) {
            $this->handleSpot($meeting['lockedSpot']);
        }

        if (isset($meeting['slot'])) {
            $this->handleSlot($meeting['slot']);
        }

        if (isset($meeting['lockedSlot'])) {
            $this->handleSlot($meeting['lockedSlot']);
        }
    }

    /**
     * This method add the meetingResult to the meetingList
     * It should be called after the handleData method that build the other list
     * needed for the creation of the meetingResult
     *
     * @param array $meeting
     */
    private function createMeeting(array &$meeting)
    {
        if (isset($meeting['@reference'])) {
            return;
        }

        $meetingResult = new MeetingResult();

        if (isset($meeting['id'])) {
            $meetingResult->requestId = $meeting['id'];
        }

        if (isset($meeting['sheetList'])
            && is_array($meeting['sheetList'])
            && isset($meeting['sheetList']['Sheet'])
            && is_array($meeting['sheetList']['Sheet'])
        ) {
            $loopIndex = 1;

            // The first sheet is the fromSheet, the second is the toSheet
            foreach ($meeting['sheetList']['Sheet'] as $sheet) {
                $sheetResult = $this->handleSheet($sheet);

                if (1 === $loopIndex) {
                    $meetingResult->sheetFrom = $sheetResult;
                } elseif (2 === $loopIndex) {
                    $meetingResult->sheetTo = $sheetResult;
                }

                ++$loopIndex;
            }
        }

        if (isset($meeting['userList'])
            && is_array($meeting['userList'])
            && isset($meeting['userList']['User'])
            && is_array($meeting['userList']['User'])
        ) {
            if (!$this->isSingle($meeting['userList']['User'])) {
                foreach ($meeting['userList']['User'] as $user) {
                    $userResult = $this->handleUser($user);

                    if (null !== $userResult) {
                        $meetingResult->addUser($userResult);
                    }
                }
            } else {
                $userResult = $this->handleUser($meeting['userList']['User']);

                if (null !== $userResult) {
                    $meetingResult->addUser($userResult);
                }
            }
        }

        if (isset($meeting['spot'])) {
            $spotResult = $this->handleSpot($meeting['spot']);

            if (null !== $spotResult) {
                $meetingResult->spot = $spotResult;
            }
        }

        if (isset($meeting['slot'])) {
            $slotResult = $this->handleSlot($meeting['slot']);

            if (null !== $slotResult) {
                $meetingResult->slot = $slotResult;
            }
        }

        $meetingResult->isBlockedSlot = isset($meeting['blockedSlot']) && 'true' === $meeting['blockedSlot'];
        $meetingResult->isBlockedSpot = isset($meeting['blockedSpot']) && 'true' === $meeting['blockedSpot'];

        $this->meetingList[] = $meetingResult;
    }

    /**
     * @param array $element
     *
     * @return bool
     */
    private function isSingle(array &$element)
    {
        return isset($element['@reference']) || isset($element['@id']);
    }
}
