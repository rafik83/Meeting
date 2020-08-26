@event
@sheet
@catalog
Feature: Display complete sheet from catalog
  As a participant, I can see a complete sheet from the catalog and request a meeting

  Scenario: I can see a complete sheet from the catalog
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And the catalog visibility is configured
    And the catalog is open since "2016-10-10 10:00:00"
    And there is a type "Fournisseur" in this event
    And there is a rule for this type and this event
    And this type is visible in catalog
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a type "Donneur d'ordre" in this event
    And there is a rule for this type and this event
    And there is a rule between this type and "Fournisseur"
    And this type is visible in catalog
    And the user "user_asddays_3@proximum.com" is created
    And there is a sheet for this type with the title "World Company Inc"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And elastica is populate
    And I am logged with "user_asddays_3@proximum.com" on front
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.catalog.available_date"
    Then I should be on this page "/fr/sheet/2/catalog"
    And I should see "Aanera"
    When I go to this page "/fr/sheet/2/catalog/display/1"
    Then I should see "Aanera"
    And I should not see "sheet.object.action.edit"
    And I should see "catalog.meeting_request.create"

  Scenario: I can not see a sheet that is not allowed in rules
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr"
    When I follow "navigation.links.catalog.available_date"
    Then I should be on this page "/fr/sheet/1/catalog"
    And I should see "Aanera"
    And I should not see "World Company Inc"
    And this page "/fr/sheet/1/catalog/display/2" returns 404
