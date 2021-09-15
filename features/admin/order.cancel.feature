@admin @order
Feature: Cancel orders
  As an Admin, I need to be able to cancel orders of a sheet

  Scenario: Cancel orders
    Given the database is purged
    And the event "Brotha From Another Motha" is created
    And there is a sheet
    And there is billing info for this sheet
    And there is an order with the amount of 123 for this sheet
    And the super admin "super-admin@example.net" is created
    And I am logged with "super-admin@example.net" on admin
    And I go to this page "/fr/event"
    When I go to this page "/fr/event/1/sheet/1#sheetOrders"
    Then I should not see "admin.order.cancelled"
    And I should see "01-01-01"
    # order number
    And I should see "admin.sheet.orders.cancel"
    When I press "admin.sheet.orders.cancel"
    Then I should be on this page "/fr/event/1/sheet/1#sheetOrders"
    And I should see "admin.order.cancelled"
