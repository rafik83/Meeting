@group @sheet
Feature: Create a sheet
  As a group manager, I can create a sheet from a previous sheet

  Scenario: Create the sheet
    Given the database is purged
    And the event "Group Event" is created
    And the user "group@example.net" is created
    And there is a group "Group" managed by this user
    And there is a sheet in this group with the title "Test"
    And I am logged with "group@example.net" on event "http://super-event.vimeet.proximum.dev"
    And I am on this page "/fr/sheets-group/1"
    And I should see "Test"
    Then I follow "group.home.link.create_sheet"
    And I should be on this page "/fr/sheets-group/1/sheet/create"
    And I fill in the following:
      | sheet_group_create_sheet_sheet | 0                     |
      | sheet_group_create_sheet_title | This is the new sheet |
    And I press "form.sheet_group_create_sheet.children.submit.label"
    Then I should be on this page "/fr/sheets-group/1"
    And I should see "flash.group.sheet.create.success"
    And I should see "This is the new sheet"
