@event @registration
Feature: Event waiting page

  Scenario: If the event has registration open date I should see the waiting page if this date is not reached
    Given the database is purged
    And the event "Event" is created
    And this event occurs today from "08:00" to "18:00"
    And the registration are not open
    When I go to this page "http://super-event.vimeet.proximum/fr/"
    Then I should be on this page "/fr/registration-not-opened"
    And I should see "event.registration_not_open"

  Scenario: If the event has registration close date, I should see the waiting page if this date is passed
    Given the database is purged
    And the event "Event" is created
    And this event occurs today from "08:00" to "18:00"
    And the registration are closed
    When I go to this page "http://super-event.vimeet.proximum/fr/"
    Then I should be on this page "/fr/registration-not-opened"
    And I should see "event.registration_closed"
    And I should see "login.link.label"

  Scenario: If the registration are open I've access to the event normally
    Given the database is purged
    And the event "Event" is created
    And this event occurs today from "08:00" to "23:59"
    And the registration are open
    When I am on the homepage of this event
    Then I should see "Event"
