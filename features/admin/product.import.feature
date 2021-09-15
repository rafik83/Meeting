@admin @product @package
Feature: Import products and package from an event
  As an Admin, I need to be able to import the products and the packages of an event to an other

  Scenario: Import products
    Given the database is purged
    And the event "Devoxx" is created
    And there is a product Participant called "Stand premium" with a price of "420" and a max quantity of 42
    And there is an option called "Option chaise" with a price of "4"
    And there is an option called "Option wifi" with a price of "8"
    And the event "Download festival" is created
    And I am logged as admin
    When I go to this page "/fr/event/2/product"
    Then I follow "admin.package.import.link"
    And I should be on this page "/fr/event/2/product/import"
    Then I select "Devoxx" from "import_products_and_template_event"
    And I press "form.import_products_and_template.children.submit.label"
    Then I should be on this page "/fr/event/2/product"
    And I should see "Option chaise"
    And I should see "Option wifi"
