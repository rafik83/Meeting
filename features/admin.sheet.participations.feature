@admin

Feature: Edit participant status
  As an admin, I can edit participant status

  Scenario: I can validate participant registration
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"
    Then I go to "/admin/fr/event/1/sheet"
    And I should see "admin.sheet.title"
    Then I check "sheet_batch_ids_4"
    And I press "form.sheet_batch.children.validate.label"
    Then the "sheet.validated" mail should be sent to "test_carnot@proximum.com"
