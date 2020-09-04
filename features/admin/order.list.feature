@admin @product @package @order
Feature: List and filter orders
  As an Admin, I need to be able to list and filter orders

  Scenario: List event orders
    Given the database is purged
    And the event "Innovative meeting" is created
    And there is a package "Package for participant" for this event
    And there is a sheet with the title "Hello World Company"
    And there is billing info for this sheet
    And there is an order with the amount of 5432 for this sheet
    And there is a sheet with the title "Aanera"
    And there is billing info for this sheet
    And there is a plan called "Formule Little" with a price of "300"
    And there is a product Participant called "Pass Jour 1" with a price of "123" and a max quantity of 1
    And this plan includes this product participant 1 time
    And this plan is assigned to this package
    And there is an order with the amount of 2066 for this sheet
    And there is this product Participant for this order
    And I am logged as admin
    When I go to this page "/fr/event/1/order"
    Then I should see "admin.order.title"

  Scenario: Filter event orders
    Given I am logged as admin
    And I am on this page "/fr/event/1/order"
    Then I should see "Aanera"
    And I should see "Hello World Company"
    When I select "Pass Jour 1" from "product"
    And I press "admin.filter"
    Then I should see "Aanera"
    And I should not see "Hello World Company"

  Scenario: Filter disabled sheet orders
    Given I am logged as admin
    And I am on this page "/fr/event/1/order?enabled=1"
    Then I should see "form.order_filter.children.sheet.enabled.label"
    And I should see "Aanera"
    And I should see "Hello World Company"
    When I go to this page "/fr/event/1/order?enabled=0"
    Then I should not see "Aanera"
    And I should not see "Hello World Company"

  Scenario: Filter order by product and enabled sheet
    Given I am logged as admin
    And I am on this page "/fr/event/1/order?enabled=1"
    When I go to this page "/fr/event/1/order?enabled=1&product=1"
    Then I should not see "Aanera"
    And I should not see "Hello World Company"
