@admin
@package
@product
Feature: Handle Update Product
  I need to be able to update a planning of an event

  Scenario: I can update a planning linked to an event
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | Admins.yml                                                               |
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event"
    When I go to this page "/admin/fr/event/1/product/4/update/planning"
    Then I should see "form.product_update_planning.children.name.label"
    And I fill in the following:
      | form.product_update_planning.children.name.label    | PlanningTitleModify |
    And I press "product_update_planning_submit"
    Then I should be on this page "/admin/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged with "test2@test.com" on admin
    When I go to this page "/admin/fr/event/1/product/4/update/planning"
    Then the "form.product_update_planning.children.name.label" field should contain "PlanningTitleModify"
