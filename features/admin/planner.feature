@admin @planner
Feature: See planner page
  As an admin, I can access the page of the planner

  Scenario: I can access the page of the planner
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Happening.yml         |
      | Admin.yml                                                                |
    When I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"
    And I follow "admin.planner.index"
    Then the response status code should be 200
    And I should be on this page "/admin/fr/event/1/planner"
