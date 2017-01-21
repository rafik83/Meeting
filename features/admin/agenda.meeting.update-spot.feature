@admin
@agenda

Feature: Update meeting spot in agenda via the API
  I need to update a meeting spot via the API

  Scenario: I can get available spots for a meeting
    Given the database is purged
    And the following fixtures files are loaded:
      | Admin.yml                                                                  |
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                    |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml   |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                            |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Spot.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-MeetingSlot.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/Unavailability/ASDDays2016-Mass.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/ASDDays2016-Meeting.yml     |
    And I am logged with "test@test.com" on admin
    When I send a GET request to "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the response should be in JSON
    When I am on "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the JSON should be equal to:
      """
      {
          "meetingId": 1,
          "spotId": 1,
          "blockedSlot": false,
          "blockedSpot": false,
          "availableSpots": [
              {"id": 2, "label": "A001"},
              {"id": 3,"label": "A002"},
              {"id": 1,"label": "G0345"}
          ]
      }
      """

  Scenario: I can change the spot for a meeting
    Given I am logged with "test@test.com" on admin
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
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot"
    Then the response status code should be 422

  Scenario: I can not change the spot for a meeting because given spot not available
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/admin/fr/event/1/agenda/meeting/1/update-spot" with body:
      """
      {
          "spotId": 4,
          "blockedSlot": false,
          "blockedSpot": false
      }
      """
    Then the response status code should be 422
