@admin
@package
@product
Feature: Handle Product
  I need to be able to create and list products of an event

  Scenario: I can create a product linked to an event
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | Admins.yml                                                               |
    And I am logged with "test2@test.com" on admin
    And I go to this page "/fr/event"
    When I go to this page "/fr/event/past"
    And I follow "admin.product.link"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.zero-result"
    When I follow "admin.product_create.plan.title"
    Then I should be on this page "/fr/event/1/product/create/plan"
    And I fill in the following:
      | form.product_create_plan.children.name.label        | ProductTitre   |
      | product_create_plan_translations_fr_title           | Titre fr       |
      | product_create_plan_translations_fr_heading         | Description fr |
      | product_create_plan_translations_en_title           | Titre en       |
      | product_create_plan_translations_en_heading         | Description en |
      | form.product_create_plan.children.unitPrice.label   | 20             |
    And I press "form.product_create_plan.children.submit.label"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.create.success"

  Scenario: I see the list of products of an event
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/fr/event/1/product"
    Then I should see "ProductTitre"
