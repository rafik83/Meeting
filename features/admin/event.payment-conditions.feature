@admin
@admin-event
Feature: See and update payment conditions
  I need to be able to see and init payment conditions for an event

  Scenario: Set payment conditions
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event/1"
    When I follow "admin.event.paymentConditions.link"
    Then the response status code should be 200
    And I check "event_payment_conditions_update_allowDeposit"
    And I fill in the following:
      | event_payment_conditions_update_minimumForDeposit | 555              |
      | event_payment_conditions_update_depositUntil      | 03/07/2016 17:30 |
      | event_payment_conditions_update_deposit           | 20               |
    And I press "event_payment_conditions_update_submit"

  Scenario: See payment conditions
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event/1"
    When I follow "admin.event.paymentConditions.link"
    Then the response status code should be 200
    And the "event_payment_conditions_update_allowDeposit" checkbox should be checked
    And the "event_payment_conditions_update_minimumForDeposit" field should contain "555"
    And the "event_payment_conditions_update_depositUntil" field should contain "03/07/2016 17:30"
    And the "event_payment_conditions_update_deposit" field should contain "20"
