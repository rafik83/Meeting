@event
@sheet
@catalog
Feature: Display complete sheet from catalog
  As a participant, I can see a complete sheet from the catalog and request a meeting

  Scenario: I can see a complete sheet from the catalog
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
    And elastica is populate
    When I am logged with "user_asddays_3@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/3"
    When I follow "navigation.links.catalog.available_date"
    Then I should be on this page "/fr/sheet/3/catalog"
    Then I should see "Aanera"
    When I go to this page "/fr/sheet/3/catalog/display/1"
    Then I should see "Aanera"
    And I should not see "sheet.object.action.edit"
    And I should see "catalog.meeting_request.create"

  Scenario: I can not see a sheet that not allowed in rules
    Given I am logged with "user_asddays_3@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I follow "navigation.links.catalog.available_date"
    Then I should not see "World Company Inc"
    And this page "/fr/sheet/3/catalog/display/3" returns 404
