@admin
@product
@package
@order
Feature: List and filter orders
  As an Admin, I need to be able to list and filter orders

  Scenario: List event orders
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Order.yml             |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/order"
    Then I should see "admin.order.title"

  Scenario: Filter event orders
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/order"
    And I should see "Onera"
    And I should see "Hello World Company"
    When I select "3" from "product"
    And I press "admin.filter"
    Then I should see "Onera"
    And I should not see "Hello World Company"



