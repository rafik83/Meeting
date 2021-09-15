@admin
@agenda

Feature: Update meeting spot and slot in agenda via the API
  I need to update a meeting spot and slot via the API

  Scenario: I can get available spots for a meeting
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there is 1 slot in this event
    And there is an active spot "A1" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is an active spot "A2" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is an active spot "A3" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is an active spot "A4" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is a meeting on spot "A2"
    # Create another sheet not concerned by the meeting
    And there is a sheet
    And spot "A4" is assigned to this sheet
    When I send a GET request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot"
    Then the response should be in JSON
    And the JSON should be equal to:
      """
      {
          "meetingId": 1,
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": false,
          "availableSpots": [
              {"id": 1, "label": "A1", "seatCapacity": 2, "slotsId": []},
              {"id": 2, "label": "A2", "seatCapacity": 2, "slotsId": []},
              {"id": 3, "label": "A3", "seatCapacity": 2, "slotsId": []},
              {"id": 4, "label": "A4", "seatCapacity": 2, "slotsId": []}
          ],
          "participants": [
               {"id": 1, "fullName": " "}
          ],
          "meetingParticipants": [
              1
          ],
          "meetingSlots": [],
          "currentSheetAvailableSlotIds": [],
          "slotId": 1,
          "metParticipantsCount": 1
      }
      """

  # This Scenario depends on previous one
  Scenario: I can change the spot for a meeting
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot" with body:
      """
      {
          "spotId": 2,
          "blockedSlot": true,
          "blockedSpot": false,
          "slotId": 1,
          "meetingParticipants": [1]
      }
      """
    Then the response status code should be 200

  # This Scenario depends on previous one
  Scenario: I can get available spots for a meeting via the API
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot"
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.error"
      """

  # This Scenario depends on previous one
  Scenario: I can not change the spot for a meeting because given spot not available
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot" with body:
      """
      {
          "spotId": 4,
          "blockedSlot": false,
          "blockedSpot": false,
          "slotId": 1,
          "meetingParticipants": [1]
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.spotNotAvailableForThisMeeting"
      """

  # This Scenario depends on previous one
  Scenario: I can not change the spot for a meeting when spot is blocked
    Given I am logged as admin
    And I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot" with body:
      """
      {
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": true,
          "slotId": 1,
          "meetingParticipants": [1]
      }
      """
    And the response status code should be 200
    When I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot" with body:
      """
      {
          "spotId": 3,
          "blockedSlot": false,
          "blockedSpot": true,
          "slotId": 1,
          "meetingParticipants": [1]
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.isBlockedSpot"
      """

  Scenario: I can get available spots for a meeting
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there is 1 slot in this event
    And there is an active spot "A1" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is an active spot "A2" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is an active spot "A3" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is a meeting on spot "A1"
    And there is a meeting on spot "A2"
    When I send a GET request to "/fr/event/1/agenda/meeting/2/sheet/1/update-spot"
    Then the response should be in JSON
    And the JSON should be equal to:
      """
      {
          "meetingId": 2,
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": false,
          "availableSpots": [
              {"id": 1, "label": "A1", "seatCapacity": 2, "slotsId": []},
              {"id": 2, "label": "A2", "seatCapacity": 2, "slotsId": []},
              {"id": 3, "label": "A3", "seatCapacity": 2, "slotsId": []}
          ],
          "participants": [
               {"id": 1, "fullName": " "}
          ],
          "meetingParticipants": [],
          "meetingSlots": [],
          "currentSheetAvailableSlotIds": [],
          "slotId": 1,
          "metParticipantsCount": 0
      }
      """

  Scenario: I can get a spot available for several meetings
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there is 1 slot in this event
    And there is an active spot "A1" with size of 1, meeting capacity of 2, seat capacity of 4
    And there is an active spot "A2" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is a meeting on spot "A1"
    And there is a meeting on spot "A2"
    When I send a GET request to "/fr/event/1/agenda/meeting/2/sheet/1/update-spot"
    Then the response should be in JSON
    And the JSON should be equal to:
      """
      {
          "meetingId": 2,
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": false,
          "availableSpots": [
              {"id": 1, "label": "A1", "seatCapacity": 4, "slotsId": []},
              {"id": 2, "label": "A2", "seatCapacity": 2, "slotsId": []}
          ],
          "participants": [
               {"id": 1, "fullName": " "}
          ],
          "meetingParticipants": [],
          "meetingSlots": [],
          "currentSheetAvailableSlotIds": [],
          "slotId": 1,
          "metParticipantsCount": 0
      }
      """

  Scenario: I can get available slots for a meeting
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there are 3 slots in this event
    And there is a meeting on slot "1"
    When I send a GET request to "/fr/event/1/agenda/meeting/1/update-slot"
    Then the JSON should be equal to:
      """
      {
          "availableSlotsId": [1, 2, 3]
      }
      """

  Scenario: I can move a meeting to available slot
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there are 3 slots in this event
    And there is a meeting on slot "3"
    When I send a POST request to "/fr/event/1/agenda/meeting/1/update-slot" with body:
      """
      {
          "slotId": 1
      }
      """
    Then the response status code should be 200

  Scenario: I can not move a meeting to unavailable slot
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there are 2 slots in this event
    And there is an active spot "A1" with size of 1, meeting capacity of 2, seat capacity of 4
    And there is a sheet
    And there is a participant for this sheet
    And there is a meeting on slot "1" and spot "A1" for this participant
    And there is a meeting on slot "2" and spot "A1" for this participant
    And I send a GET request to "/fr/event/1/agenda/meeting/1/update-slot"
    And the JSON should be equal to:
      """
      {
          "availableSlotsId": [1]
      }
      """
    When I send a POST request to "/fr/event/1/agenda/meeting/1/update-slot" with body:
      """
      {
          "slotId": 2
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSlot.slotNotAvailableForThisMeeting"
      """

  Scenario: I can not change the slot for a meeting when slot is blocked
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there are 2 slots in this event
    And there is a meeting on slot "1"
    And I send a POST request to "/fr/event/1/agenda/meeting/1/sheet/1/update-spot" with body:
      """
      {
          "spotId": 1,
          "blockedSlot": true,
          "blockedSpot": false,
          "slotId": 1,
          "meetingParticipants": [1]
      }
      """
    And the response status code should be 200
    When I send a POST request to "/fr/event/1/agenda/meeting/1/update-slot" with body:
      """
      {
          "slotId": 2
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSlot.meetingIsBlockedSlot"
      """
