@event @agenda @availability
Feature: Availability
  As a participant, I can add and remove an availability

  Scenario: I can add my availabilities
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And this event occurs the "2016-10-12" from "08:00" to "18:00"
    And the agenda is open
    And there is a type "Fournisseur" in this event
    And this type has availability management enabled
    And the user "user_asddays_3@proximum.com" is created
    And there is a sheet for this type with the title "World company"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And I am logged with "user_asddays_3@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr"
    # Then print last response
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/agenda"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should not see "unavailability.title"
    And I should not see "agenda.unavailability.add"
    And I should see "agenda.availability.define"
