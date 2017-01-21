@admin
@agenda

Feature: Update meeting spot in agenda via the API
  I need to update a meeting spot via the API

  Scenario: I can get available spots for a meeting
    Given the database is empty
    And I am logged as admin
    And the event "Best of web" is created
    And there are 10 slots in this event
    And there is an active spot "A1" with meeting capacity of 1, seat capacity of 2
    And there is an active spot "A2" with meeting capacity of 1, seat capacity of 2
    And there is an active spot "A3" with meeting capacity of 1, seat capacity of 2
    And there is an active spot "A4" with meeting capacity of 1, seat capacity of 2
    And there is a meeting on spot "A2"
    And spot "A4" is assigned to another sheet
    When I send a GET request to "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the response should be in JSON
    When I am on "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the JSON should be equal to:
      """
      {
          "meetingId": 1,
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": false,
          "availableSpots": [
              {"id": 1, "label": "A1"},
              {"id": 2,"label": "A2"},
              {"id": 3,"label": "A3"}
          ]
      }
      """

  Scenario: I can change the spot for a meeting
    Given I am logged as admin
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot" with body:
      """
      {
          "spotId": 2,
          "blockedSlot": true,
          "blockedSpot": false
      }
      """
    Then the response status code should be 200

  Scenario: I can get available spots for a meeting via the API
    Given I am logged as admin
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.error"
      """

  Scenario: I can not change the spot for a meeting because given spot not available
    Given I am logged as admin
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot" with body:
      """
      {
          "spotId": 4,
          "blockedSlot": false,
          "blockedSpot": false
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.spotNotAvailableForThisMeeting"
      """

  Scenario: I can not change the spot for a meeting when spot is blocked
    Given I am logged as admin
    And I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot" with body:
      """
      {
          "spotId": 2,
          "blockedSlot": false,
          "blockedSpot": true
      }
      """
    And the response status code should be 200
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot" with body:
      """
      {
          "spotId": 3,
          "blockedSlot": false,
          "blockedSpot": true
      }
      """
    Then the response status code should be 422
    And the JSON should be equal to:
      """
          "admin.agenda.meeting.updateSpot.isBlockedSpot"
      """
