Feature: Order fields type template field
  I need to be able to add and update a text field

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | Admin.yml        |
    Given I am logged with "test@test.com" on admin

  Scenario: I can order fields
    Given I am on this page "/admin/fr/event/1/type/1/form"
    And the "order[563caf1d9b1cb]" field should contain "1"
    And the "order[563caf2746398]" field should contain "2"
    When I fill in the following:
      | order[563caf1d9b1cb] | 10 |
      | order[563caf2746398] | 1  |
    And I press "update-order-submit-participant-default"
    Then I should see "flash.admin.type_template_field.order.success"
    And the "order[563caf1d9b1cb]" field should contain "4"
    And the "order[563caf2746398]" field should contain "1"
