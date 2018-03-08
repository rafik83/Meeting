@event @sheet @catalog
Feature: Search sheet in catalog
  As a participant, I can filter the sheet list in catalog by position

  Scenario: I can search and filter sheet by catalog by position
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-SearchFacet.yml       |
    And elastica is populate
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
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
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
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
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
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
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    When I go to this page "/fr/sheet/2/catalog?objective=supply"
    Then I should see "1" in the title of the ".total-result" element
    And I should see "Aanera"
    But I should not see "World Company Inc"
    And I should not see "Hello World Company"
    When I go to this page "/fr/sheet/2/catalog?objective=need"
    Then I should see "2" in the title of the ".total-result" element
    And I should see "Hello World Company"
    And I should see "Aanera"
    But I should not see "World Company Inc"
    When I go to this page "/fr/sheet/2/catalog?objective=supply&objective=need"
    Then I should see "2" in the title of the ".total-result" element
    And I should see "Hello World Company"
    And I should see "Aanera"
    But I should not see "World Company Inc"
