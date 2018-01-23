@event @catalog @dday
Feature: Dday catalog

  Scenario: I need to validate my phone to manage meeting request
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
    And this event occurs today from "00:00" to "23:59"
    And the tip "Confirmation telephone" is created
    And a tip "Confirmation telephone" is enabled on confirmation phone context for this type
    And elastica is populate
    When I am logged with "user@elao.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/"
    Then I should see "navigation.category.catalog"
    When I follow "navigation.category.catalog"
    Then I should see "catalog.meeting_request.create"
    When I follow "catalog.meeting_request.create"
    Then I should be on this page "/fr/account/sheet/2/participant/1/validate/phone"
    And I should see "flash.event.user_event_phone.confirmationNeeded"


