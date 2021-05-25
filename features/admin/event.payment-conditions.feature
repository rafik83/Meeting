@admin
@admin-event
Feature: See and update payment conditions
  I need to be able to see and init payment conditions for an event

  Scenario: Set payment conditions
    Given the database is purged
    And the event "L'argent ne fait pas le bonheur" is created
    And I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.paymentConditions.link"
    Then the response status code should be 200
    And I check "event_payment_conditions_update_allowDeposit"
    And I fill in the following:
      | event_payment_conditions_update_minimumForDeposit | 555              |
      | event_payment_conditions_update_depositUntil      | 03/07/2016 17:30 |
      | event_payment_conditions_update_deposit           | 20               |
    And I check the "form.payment_mode_choice.children.paymentMode.none" radio
    And I uncheck "bank_transfer"
    And I uncheck "bank_check"
    And I press "event_payment_conditions_update_submit"
    And I should see "validators.paymentModesChoiceEmpty"
    And I check the "form.payment_mode_choice.children.paymentMode.paypal" radio
    And I press "event_payment_conditions_update_submit"
    And I should see "flash.admin.event.paymentConditions.update.success"


  Scenario: See payment conditions
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.paymentConditions.link"
    Then the response status code should be 200
    And the "event_payment_conditions_update_allowDeposit" checkbox should be checked
    And the "event_payment_conditions_update_minimumForDeposit" field should contain "555"
    And the "event_payment_conditions_update_depositUntil" field should contain "03/07/2016 17:30"
    And the "event_payment_conditions_update_deposit" field should contain "20"
