@event
@catalog
Feature: View menu and button
When I am logged

  Scenario: I see the catalog's button
    Given the database is purged
    And the event "techinnov" is created
    And there is a type in this event
    And there is a rule for this type and this event
    And the catalog is open since "2017-01-01"
    And there is a sheet for this type with the title "Elao"
    And this sheet is in catalog
    And there is a sheet for this type with the title "Proximum"
    And this sheet is in catalog
    And the user "user@elao.com" is created
    And there is a participant for this sheet and this user
    When I am logged with "user@elao.com" on event "http://super-event.vimeet.proximum"
    Then I go to this page "/fr/sheet/2"
    And I should see "navigation.category.catalog"

  Scenario: I don't see the catalog's button
    Given the database is purged
    And the event "techinnov" is created
    And there is a type in this event
    And the catalog is open since "2017-01-01"
    And there is a sheet for this type with the title "Elao"
    And this sheet is in catalog
    And there is a sheet for this type with the title "Proximum"
    And this sheet is in catalog
    And the user "user@elao.com" is created
    And there is a participant for this sheet and this user
    When I am logged with "user@elao.com" on event "http://super-event.vimeet.proximum"
    Then I go to this page "/fr/sheet/2"
    And I should not see "navigation.category.catalog"
