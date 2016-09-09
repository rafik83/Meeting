@event
@sheet
@catalog
Feature: Display complete sheet from catalog
  As a participant, I can see a complete sheet from the catalog and request a meeting

  Scenario: I can see a complete sheet from the catalog
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
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "navigation.links.catalog.available_date"
    And I go to this page "/fr/catalog/sheet/1"
    And I should see "Onera"
    And I should not see "sheet.object.action.edit"
    And I should not see "sheet.request_meeting"
    When I follow "navigation.links.catalog.available_date"
    And I go to this page "/fr/catalog/sheet/2"
    And I should not see "Onera"
    And I should not see "sheet.object.action.edit"
    And I should see "sheet.request_meeting"
