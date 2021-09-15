@admin
@package
@product
Feature: Handle Update Product
  I need to be able to update a planning of an event

  Scenario: I can update a planning linked to an event
    Given the database is purged
    And the event "CES" is created
    And I am logged as admin
    And there is a product Planning called "Planning de RDV PO" with a price of "4200" and a max quantity of 1
    When I go to this page "/fr/event/1/product/1/update/planning"
    Then I should see "form.product_update_planning.children.name.label"
    And I fill in the following:
      | form.product_update_planning.children.name.label    | PlanningTitleModify |
    And I press "product_update_planning_submit"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged as admin
    When I go to this page "/fr/event/1/product/1/update/planning"
    Then the "form.product_update_planning.children.name.label" field should contain "PlanningTitleModify"
