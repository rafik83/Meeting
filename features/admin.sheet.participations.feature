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
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
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
    And I should see "admin.sheet.title.count"
    Then I check "sheet_batch_ids_4"
    And I press "form.sheet_batch.children.validate.label"
    Then the "sheet.validated" mail should be sent to "test_carnot@proximum.com"

  Scenario: I can enable or disable participant registration
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to "/admin/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_4"
    When I press "form.sheet_batch.children.disable.label"
    Then I should be on this page "/admin/fr/event/1/sheet"
    And I should see "admin.sheet.disable"

  Scenario: I can add or remove a sheet from the catalog
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to "/admin/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_4"
    When I press "form.sheet_batch.children.addCatalog.label"
    Then I should be on this page "/admin/fr/event/1/sheet"
    And I should see "✓" in the "#sheet-4" element
