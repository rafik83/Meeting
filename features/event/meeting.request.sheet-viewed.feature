@event @sheet @catalog
Feature: Filter by sheet viewed on meeting request list
  As a participant, I can filter the meeting request by sheet saw or sheet which saw my participation

  Scenario: I can see a complete sheet from the catalog
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And the catalog visibility is configured
    And the catalog is open since "2020-09-10 10:00:00"
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
    And there is a request between "Aanera" and "World Company Inc"
    And elastica is populate
    And I am logged with "user_asddays_3@proximum.com" on front
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I go to this page "/fr/sheet/2/catalog/display/1"
    Then I should see "Aanera"
    # filter on sheet saw
    When I go to this page "/fr/sheet/2/meeting/request?state=all&sheetVisit=sheetSaw&orderBy=alphabetical"
    Then I should see "Aanera"
    # filter on sheet who saw me
    When I go to this page "/fr/sheet/2/meeting/request?state=all&sheetVisit=viewedBySheet&orderBy=alphabetical"
    Then I should not see "Aanera"
