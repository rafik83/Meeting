Feature: Handle Update Product
  I need to be able to update a product of an event

  Scenario: I can update a participant linked to an event
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | Admins.yml                                                               |
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event"
    Then I go to this page "/admin/fr/event/1/product/3/update/participant"
    And I should see "form.product_update_participant.children.name.label"
    Then I fill in the following:
      | form.product_update_participant.children.name   | ParticipantTitleModify |
    And I press "product_update_participant_submit"
    Then I should be on this page "/admin/fr/event/1/product"
    And I should see "admin.product.update.success"

  Scenario: I see my updated product
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event/1/product/3/update/participant"
    Then the "form.product_update_participant.children.name.label" field should contain "ParticipantTitleModify"
