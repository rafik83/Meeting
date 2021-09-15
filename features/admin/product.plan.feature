@admin
@package
@product
Feature: Handle Product
  I need to be able to create and list products of an event

  Scenario: I can create a product linked to an event
    Given the database is purged
    And the event "Foire de Paris" is created
    And I am logged as admin
    When I go to this page "/fr/event"
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
    Given I am logged as admin
    And I go to this page "/fr/event/1/product"
    Then I should see "ProductTitre"

  Scenario: I can update a plan
    Given the database is purged
    And the event "PWA Meetup" is created
    And there is a plan called "Formule Full" with a price of "100"
    And the super admin "admin@example.net" is created
    And I am logged with "admin@example.net" on admin
    When I go to this page "/fr/event/1/product/1/update/plan"
    Then the "product_update_plan[name]" field should contain "Formule Full"
    And I fill in the following:
      | form.product_update_plan.children.name.label        | Formule Full Bis |
      | product_update_plan_translations_fr_title           | Titre fr         |
      | product_update_plan_translations_fr_heading         | Description fr   |
      | product_update_plan_translations_en_title           | Titre en         |
      | product_update_plan_translations_en_heading         | Description en   |
      | form.product_update_plan.children.unitPrice.label   | 20               |
    And I press "form.product_update_plan.children.submit.label"
    And I should be on this page "/fr/event/1/product"
    And I should see "Formule Full Bis"
