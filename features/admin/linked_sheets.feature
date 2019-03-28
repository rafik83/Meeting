@admin @linked_sheets

Feature: Manage linked sheets
  As an Admin I need to be able to manage linked sheets

  Scenario: I can't link only one sheet
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    Then I should see "validators.linkedSheets.add.notEnoughSheets"

  Scenario: I can't link from different types
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I additionally select "2" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    Then I should see "validators.linkedSheets.add.notUniqueType"

  Scenario: I can link sheets
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I additionally select "1" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    And I should be on this page "/fr/event/1/linked-sheets/list"
    And I should see "Aanera"
    And I should see "Hello World Company"
