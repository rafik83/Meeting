@admin @linked_sheets

Feature: Manage linked sheets
  As an Admin I need to be able to manage linked sheets

  Scenario: I can't link only one sheet
    Given the database is purged
    And the event "Les rendez-vous CARNOT 2019" is created
    And there is a type in this event
    And there is a rule for this type and this event
    And there is a sheet for this type with the title "Proximum"
    And I am logged as admin
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    Then I should see "validators.linkedSheets.add.notEnoughSheets"

  Scenario: I can't link from different types
    Given the database is purged
    Given the event "Les rendez-vous CARNOT 2019" is created
    And I am logged as admin
    And there is a type in this event
    And there is a sheet for this type with the title "Fairness Coop"
    And there is a type in this event
    And there is a sheet for this type with the title "Acme Corp"
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I additionally select "1" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    Then I should see "validators.linkedSheets.add.notUniqueType"

  Scenario: I can link sheets
    Given the database is purged
    Given the event "Les rendez-vous CARNOT 2019" is created
    And I am logged as admin
    And there is a type in this event
    And there is a sheet for this type with the title "Aanera"
    And there is a sheet for this type with the title "Hello World Company"
    And I am on this page "/fr/event/1/linked-sheets/list"
    Then I follow "admin.linked_sheets.create"
    And I should be on this page "/fr/event/1/linked-sheets/create"
    And I select "0" from "create_linked_sheets_sheetViews"
    And I additionally select "1" from "create_linked_sheets_sheetViews"
    And I press "form.create_linked_sheets.children.submit.label"
    And I should be on this page "/fr/event/1/linked-sheets/list"
    And I should see "Aanera"
    And I should see "Hello World Company"
