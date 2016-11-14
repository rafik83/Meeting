@admin @product @package
Feature: Import products and package from an event
  As an Admin, I need to be able to import the products and the packages of an event to an other

  Scenario: Import products
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
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/2/product"
    Then I follow "admin.package.import.link"
    And I should be on this page "/admin/fr/event/2/product/import"
    Then I select "ASD Days" from "import_products_and_template_event"
    And I press "form.import_products_and_template.children.submit.label"
    Then I should be on this page "/admin/fr/event/2/product"
    And I should see "Option chaise"
    And I should see "Option A"
