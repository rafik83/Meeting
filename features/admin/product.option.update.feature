@admin
@product
@package
Feature: Handle Update Product
  I need to be able to update a product of an event

  Scenario: I can update a product linked to an event
    Given the database is purged
    And the event "Salon de l'agriculture" is created
    And there is a product Participant called "Stand premium" with a price of "420" and a max quantity of 42
    And there is an attributable option called "Option électricité 5KW" with a quantity max of "1" and a price of "40"
    And I am logged as admin
    When I go to this page "/fr/event/1/product/2/update/option"
    Then I should see "form.product_update_option.children.name.label"
    And I fill in the following:
      | product_update_option_name | ProductTitleModify |
    When I press "product_update_option_submit"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged as admin
    And I go to this page "/fr/event/1/product/2/update/option"
    Then the "form.product_update_option.children.name.label" field should contain "ProductTitleModify"

  Scenario: I can update product price of a not purchased product
    Given I am logged as admin
    And I go to this page "/fr/event/1/product/2/update/option"
    Then I should see "form.product_update_option.children.unitPrice.label"
    And I fill in "form.product_update_option.children.unitPrice.label" with "15"
    When I press "product_update_option_submit"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.update.success"
    When I go to this page "/fr/event/1/product/2/update/option"
    Then the "form.product_update_option.children.unitPrice.label" field should contain "15"
