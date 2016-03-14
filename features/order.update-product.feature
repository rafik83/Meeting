Feature: Update a product purchased
  I need to be able to update a product purchased

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | Template.yml                                           |
      | app/Event.yml                                          |
      | app/Type.yml                                           |
      | User.yml                                               |
      | OneSheetOneParticipantWithBillingDataForProForma.yml   |
    Given I am logged with "test-3@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/sheet/1/orders"
    And I follow "event.sheet.summary.title"
    Then the response status code should be 200
    And I should be on this page "/fr/sheet/1/summary"
    And I follow "event.sheet.summary.update_product"
    Then the response status code should be 200
    And I should see "event.sheet.update_product.title"

  Scenario: I can decrement quantity of a purchased product and create a negative order
    Given I fill in "update_product[productItem][quantity]" with "0"
    And I press "form.update_product.children.submit.label"
    Then I should be on this page "/fr/sheet/1/orders"
    And the response status code should be 200
    And I should see "event.sheet.listOrders.title"
    And I should see "flash.package.update_product.created_negative_order"

  Scenario: I can increment quantity of a purchased product and add product in cart
    Given I fill in "update_product[productItem][quantity]" with "2"
    And I press "form.update_product.children.submit.label"
    Then I should be on this page "/fr/sheet/1/cart"
    And the response status code should be 200
    And I should see "event.sheet.cart.title"
    And I should see "flash.package.update_product.added_updated_product_to_cart"
    And I should see "1" in the column "event.sheet.cart.column.quantity" for the row containing "Stand 4m²"
