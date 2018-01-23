@content
Feature: See terms of sale
  I need to be able to see the terms of sale of my event

  Scenario: See terms of sale
    Given the database is purged
    And the event "Terms And Conditions Party" is created
    And there is a type in this event
    And there is terms of sale for this event
    And there is a sheet for this type with the title "Elao"
    And the user "user@elao.com" is created
    And there is a participant for this sheet and this user
    When I am logged with "user@elao.com" on event "http://super-event.vimeet.proximum"
    Then I am on this page "/fr/sheet/1/terms-of-sale"
    And I should see "foobar"
