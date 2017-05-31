@admin
@agenda

Feature: I can update a dispatched mass assignment unavaibility vie the API

  Scenario: I can see a dispatched mass assignment unavaibility
    Given the database is purged
    And the following fixtures files are loaded:
      | Admin.yml                                                                            |
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                              |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml                    |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml                         |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml                       |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml                      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                                      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Spot.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml                         |
      | SheetWhichHaveAnAssignedSpot.yml                                                     |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-MeetingSlot.yml                   |
      | @InfrastructureBundle/DataFixtures/ORM/Unavailability/ASDDays2016-Mass.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/ASDDays2016-Meeting.yml               |
    And I am logged with "test@test.com" on admin
    When I send a GET request to "/fr/event/1/agenda/mass/1/detail"
    Then the JSON should be equal to:
      """
      {
          "id": 1,
          "title": "",
          "massBegin": {
            "date": "2016-10-12 13:00:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "massEnd": {
            "date": "2016-10-12 14:00:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "begin": {
            "date": "2016-10-12 13:00:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "end": {
            "date": "2016-10-12 14:00:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "enabled": true,
          "eventTimezone": "Europe/Paris",
          "serverTimezone": "UTC"
      }
      """

  Scenario: I can update a dispatched mass assignment
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
    | key     | value            |
    | begin   | 12/10/2016 13:30 |
    | end     | 12/10/2016 13:45 |
    | enabled | true             |
    Then the response status code should be 204

  Scenario: I can disable a dispatched mass assignment
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 13:00 |
      | end     | 12/10/2016 13:30 |
      | enabled | false            |
    Then the response status code should be 204

  Scenario: I cannot update a dispatched mass assignment that overlap on meeting
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 01:00 |
      | end     | 12/10/2016 02:00 |
      | enabled | true             |
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.outOfMassSlot"
    """

  Scenario: I cannot update a dispatched mass assignment out of bounded mass
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 11:00 |
      | end     | 12/10/2016 12:00 |
      | enabled | true             |
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.meetingOrHappeningOnSlot"
    """
  Scenario: I cannot update a dispatched mass assignment without POST parameters:
    Given I am logged with "test@test.com" on admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update"
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.missingData"
    """
