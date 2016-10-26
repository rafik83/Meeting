@event @sheet @catalog
Feature: Search sheet in catalog
  As a participant, I can filter the sheet list in catalog by position

  Scenario: I can search and filter sheet by catalog by position
    Given the database is empty
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
    And elastica is populate
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "navigation.links.catalog.available_date"
    Then I should see "Onera"
    And I should see "World Company Inc"
    And I should see "Hello World Company"
    When I go to this page "/fr/catalog?position[]=position98"
    Then I should see "Onera"
    And I should not see "World Company Inc"
    And I should not see "Hello World Company"

  Scenario: I can search and filter sheet by localization
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "navigation.links.catalog.available_date"
    Then I should see "Onera"
    And I should see "World Company Inc"
    And I should see "Hello World Company"
    When I go to this page "/fr/catalog?localization=lyon"
    Then I should see "World Company Inc"
    But I should not see "Onera"
    And I should not see "Hello World Company"
