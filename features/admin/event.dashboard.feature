@admin @dashboard
Feature: See the dashboard of an event
  I need to be able to see the dashboard of an event

  Scenario: See dashboard of event
    Given the database is purged
    And the event "Best of web" is created
    And there is an order with the amount of 123 and VAT is applicable
    And there is an order with the amount of 200 and VAT is not applicable
    And I am logged as admin
    When I go to this page "/fr/event/1/dashboard"
    Then I should see "admin.event.dashboard.title"
    And I should see "323,00 €" in the ".dashboard-total-orders" element
    And I should see "0,00 €" in the ".dashboard-total-paid" element
    And I should see "347,60 €" in the ".dashboard-total-remaining-to-pay" element
    And I should see "admin.sheet.dashboard.by_type.sheets"
    And I should see "admin.sheet.dashboard.by_type.total"
    And I should see "admin.sheet.dashboard.by_type.meetings_not_evaluated"
