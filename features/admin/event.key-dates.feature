@admin @event
Feature: I can manage key dates for an event

  Scenario: I can see and update key dates for an event
    Given the database is purged
    And the event "Concert de Francky Vincent" is created
    And this event occurs the "2016-10-12" from "08:00" to "20:00"
    When I am logged as admin
    And I go to this page "/fr/event/1/dates"
    Then the response status code should be 200
    When I fill in the following:
      | event_configure_date_catalogOnlineDate                | 12/10/2016 13:30 |
      | event_configure_date_happeningsOpenDate               | 13/10/2016 13:40 |
      | event_configure_date_schedulePublishDate              | 14/10/2016 13:30 |
      | event_configure_date_closeMeetingRequestDate          | 15/10/2016 13:30 |
      | event_configure_date_closeAnsweringMeetingRequestDate | 16/10/2016 13:30 |
      | event_configure_date_smsActivationDate                | 17/10/2016 13:30 |
      | event_configure_date_agendaOnlineDate                 | 18/10/2016 13:30 |
      | event_configure_date_registrationOpenDate             | 10/10/2016 12:00 |
      | event_configure_date_registrationCloseDate            | 18/10/2016 21:00 |
      | event_configure_date_networkingOpenDate               | 10/10/2016 12:00 |
      | event_configure_date_networkingCloseDate              | 18/10/2016 21:00 |
      | event_configure_date_callVisioOpenDate                | 10/10/2016 12:00 |
      | event_configure_date_callVisioCloseDate               | 18/10/2016 21:00 |
    And I press "event_configure_date_submit"
    Then I should see "flash.admin.event.configure_dates.success"
    When I go to this page "/fr/event/1/dates"
    Then the "event_configure_date_catalogOnlineDate" field should contain "12/10/2016 13:30"
    And the "event_configure_date_happeningsOpenDate" field should contain "13/10/2016 13:40"
    And the "event_configure_date_schedulePublishDate" field should contain "14/10/2016 13:30"
    And the "event_configure_date_closeMeetingRequestDate" field should contain "15/10/2016 13:30"
    And the "event_configure_date_closeAnsweringMeetingRequestDate" field should contain "16/10/2016 13:30"
    And the "event_configure_date_smsActivationDate" field should contain "17/10/2016 13:30"
    And the "event_configure_date_agendaOnlineDate" field should contain "18/10/2016 13:30"
    And the "event_configure_date_registrationOpenDate" field should contain "10/10/2016 12:00"
    And the "event_configure_date_registrationCloseDate" field should contain "18/10/2016 21:00"
    And the "event_configure_date_networkingOpenDate" field should contain "10/10/2016 12:00"
    And the "event_configure_date_networkingCloseDate" field should contain "18/10/2016 21:00"
    And the "event_configure_date_callVisioOpenDate" field should contain "10/10/2016 12:00"
    And the "event_configure_date_callVisioCloseDate" field should contain "18/10/2016 21:00"
