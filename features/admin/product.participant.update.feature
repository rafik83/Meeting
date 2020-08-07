@admin
@product
@package
Feature: Handle Update Product
  I need to be able to update a product of an event

  Scenario: I can update a participant linked to an event
    Given the database is purged
    And the event "SIEL" is created
    And there is a product Participant called "Stand premium" with a price of "420" and a max quantity of 42
    And I am logged as admin
    And I go to this page "/fr/event"
    When I go to this page "/fr/event/1/product/1/update/participant"
    Then I should see "form.product_update_participant.children.name.label"
    And I fill in the following:
      | form.product_update_participant.children.name   | ParticipantTitleModify |
    When I press "product_update_participant_submit"
    Then I should be on this page "/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged as admin
    When I go to this page "/fr/event/1/product/1/update/participant"
    Then the "form.product_update_participant.children.name.label" field should contain "ParticipantTitleModify"
