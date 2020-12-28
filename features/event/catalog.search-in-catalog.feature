@event @sheet @catalog
Feature: Search sheet in catalog
  As a participant, I can filter the sheet list in catalog by position

  Scenario: I can search and filter sheet by catalog by position
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And the catalog visibility is configured
    And the catalog is open since "2016-10-10 10:00:00"
    And there is a type "Fournisseur" in this event
    And there is a rule for this type and this event
    And this type is visible in catalog
    And this type can view display analytics on catalog
    And there is a "position" search facet
    And there is a "localization" search facet
    And there is a "keywords" search facet
    And there is a position nomenclature
    And there is a registration template
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And this participant has "position98" position
    And this sheet has "Paris" as city
    And this sheet has supply services
    And this sheet has needs
    And this sheet is validated
    And this sheet is in catalog
    And the user "user_asddays_2@proximum.com" is created
    And there is a sheet for this type with the title "World Company Inc"
    And there is a participant for this sheet and this user
    And this participant has "position97" position
    And this sheet has "Lyon" as city
    And this sheet supply computing
    And this sheet needs prototyping
    And this sheet is validated
    And this sheet is in catalog
    And the user "user_asddays_3@proximum.com" is created
    And there is a sheet for this type with the title "Hello World Company"
    And there is a participant for this sheet and this user
    And this participant has "position96" position
    And this sheet has "Paris" as city
    And this sheet is validated
    And this sheet is in catalog
    And elastica is populate
    When I am logged with "user_asddays_2@proximum.com" on front
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.catalog.available_date"
    Then I should see "Aanera"
    And I should see "World Company Inc"
    And I should see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?position[]=position98"
    Then I should see "1" in the title of the ".total-result" element
    Then I should see "Aanera"
    But I should not see "World Company Inc"
    And I should not see "Hello World Company"

  Scenario: I can search and filter sheet by localization
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_2@proximum.com" on front
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.catalog.available_date"
    Then I should see "Aanera"
    And I should see "World Company Inc"
    And I should see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?localization=lyon"
    Then I should see "1" in the title of the ".total-result" element
    And I should see "World Company Inc"
    But I should not see "Aanera"
    And I should not see "Hello World Company"

  Scenario: I can search and filter sheet by keyword
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    Given I am logged with "user_asddays_2@proximum.com" on front
    And I go to this page "/fr"
    And I go to this page "/fr/catalog"
    Then I should see "3" in the title of the ".total-result" element
    And I should see "Aanera"
    And I should see "World Company Inc"
    And I should see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?content=Aanera"
    Then I should see "1" in the title of the ".total-result" element
    And I should see "Aanera"
    But I should not see "World Company Inc"
    And I should not see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?content=unknowsheet"
    Then I should see "0" in the title of the ".total-result" element
    Then I should see "catalog.noResult"

  Scenario: I can search and filter sheet by supply or need
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    Given I am logged with "user_asddays_2@proximum.com" on front
    And I go to this page "/fr"
    When I go to this page "/fr/sheet/2/catalog?objective[]=supply"
    Then I should see "1" in the title of the ".total-result" element
    And I should see "Aanera"
    But I should not see "World Company Inc"
    And I should not see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?objective[]=need"
    Then I should see "0" in the title of the ".total-result" element
    And I should not see "Hello World Company"
    And I should not see "Aanera"
    But I should not see "World Company Inc"
    When I go to this page "/fr/sheet/2/catalog?objective[]=supply&objective[]=need"
    Then I should see "1" in the title of the ".total-result" element
    And I should see "Aanera"
    And I should not see "Hello World Company"
    But I should not see "World Company Inc"

  Scenario: I can search and filter sheet by saw / who saw me
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    Given I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/catalog/display/2"
    # filter on sheet saw
    And I go to this page "/fr/sheet/1/catalog?sheetVisit[]=sheetSaw"
    Then I should see "World Company Inc"
    Then I should not see "Hello World Company"
    Then I should not see "Aanera"
    # filter on sheet who saw me
    When I am logged with "user_asddays_2@proximum.com" on front
    And I go to this page "/fr/sheet/2/catalog?sheetVisit[]=viewedBySheet"
    Then I should see "Aanera"
    Then I should not see "Hello World Company"
    Then I should not see "World Company Inc"
