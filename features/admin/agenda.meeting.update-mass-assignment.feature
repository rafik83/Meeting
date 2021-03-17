@admin
@agenda

Feature: I can update a dispatched mass assignment unavaibility via the API

  Scenario: I can see a dispatched mass assignment unavaibility
    Given the database is purged
    And the event "Best of Planner" is created
    And there is a type "Fournisseur" in this event
    And the user "user_asddays_2@proximum.com" is created
    And this user is declared in this event
    And there is a sheet
    And there is a participant for this sheet and this user
    And this event occurs the "2016-10-12" from "08:00" to "18:00"
    And there is a mass unavailability category called "Pause" for this event
    And there is a mass unavailability "Cocktail" for this type the 2016-10-12 from 11:00 to 12:00
    And there is a meeting slot from 2016-10-12 11:30:00 to 2016-10-12 11:40:00
    And this slot is assigned to this mass unavailability for this user
    And there is a meeting slot from 2016-10-12 11:50:00 to 2016-10-12 12:00:00
    And this slot is assigned to this mass unavailability for this user
    And I am logged as admin
    When I send a GET request to "/fr/event/1/agenda/mass/1/detail"
    Then the JSON should be equal to:
      """
      {
          "id": 1,
          "title": "Cocktail",
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
            "date": "2016-10-12 13:30:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "end": {
            "date": "2016-10-12 13:40:00.000000",
            "timezone_type": 3,
            "timezone": "Europe/Paris"
          },
          "enabled": true,
          "eventTimezone": "Europe/Paris",
          "serverTimezone": "UTC"
      }
      """

  Scenario: I can update a dispatched mass assignment
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
    | key     | value            |
    | begin   | 12/10/2016 13:30 |
    | end     | 12/10/2016 13:45 |
    | enabled | true             |
    Then the response status code should be 204

  Scenario: I can disable a dispatched mass assignment
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 13:00 |
      | end     | 12/10/2016 13:30 |
      | enabled | false            |
    Then the response status code should be 204

  Scenario: I cannot update a dispatched mass assignment that overlap on meeting
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 09:00 |
      | end     | 12/10/2016 10:00 |
      | enabled | true             |
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.outOfMassSlot"
    """
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 11:00 |
      | end     | 12/10/2016 12:00 |
      | enabled | true             |
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.outOfMassSlot"
    """

  Scenario: I cannot update a dispatched mass assignment out of bounded mass
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update" with parameters:
      | key     | value            |
      | begin   | 12/10/2016 10:15 |
      | end     | 12/10/2016 10:20 |
      | enabled | true             |
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.outOfMassSlot"
    """

  Scenario: I cannot update a dispatched mass assignment without POST parameters:
    Given I am logged as admin
    When I send a POST request to "/fr/event/1/agenda/mass/1/update"
    Then the response status code should be 422
    And the JSON should be equal to:
    """
      "admin.agenda.meeting.updateMassAssignment.missingData"
    """
