@admin
@product
@package
Feature: Handle Product
  I need to be able to create and list products of an event

  Scenario: I can create a product linked to an event
    Given the database is purged
    And the event "TedX" is created
    And I am logged as admin
    When I go to this page "/fr/event"
    # When I go to this page "/fr/event/past"
    And I follow "admin.product.link"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.zero-result"
    Then I follow "admin.product_create.option.title"
    And I should be on this page "/fr/event/1/product/create/option"
    And I fill in the following:
      | form.product_create_option.children.name.label        | ProductTitre |
      | product_create_option_translations_fr_title           | Titre fr     |
      | product_create_option_translations_en_title           | Titre en     |
      | form.product_create_option.children.unitPrice.label   | 20           |
      | form.product_create_option.children.vat.label         | 19           |
      | form.product_create_option.children.quantityMax.label | 4            |
    And I press "form.product_create_option.children.submit.label"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.create.success"

  Scenario: I see the list of products of an event
    Given I am logged as admin
    And I go to this page "/fr/event/1/product"
    Then I should see "ProductTitre"
