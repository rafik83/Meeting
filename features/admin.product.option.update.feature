@admin
Feature: Handle Update Product
  I need to be able to update a product of an event

  Scenario: I can update a product linked to an event
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
    When I go to this page "/admin/fr/event/1/product/6/update/option"
    Then I should see "form.product_update_option.children.name.label"
    And I fill in the following:
      | product_update_option_name | ProductTitleModify |
    When I press "product_update_option_submit"
    Then I should be on this page "/admin/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event/1/product/6/update/option"
    Then the "form.product_update_option.children.name.label" field should contain "ProductTitleModify"

  Scenario: I can update product price of a not purchased product
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event/1/product/6/update/option"
    Then I should see "form.product_update_option.children.unitPrice.label"
    And I fill in "form.product_update_option.children.unitPrice.label" with "15"
    When I press "product_update_option_submit"
    Then I should be on this page "/admin/fr/event/1/product"
    And I should see "admin.product.update.success"
    When I go to this page "/admin/fr/event/1/product/6/update/option"
    Then the "form.product_update_option.children.unitPrice.label" field should contain "15"
