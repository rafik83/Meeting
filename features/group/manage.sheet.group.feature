@group @sheet
Feature: Manage sheet group
  As a group manager, I can create a sheet from a previous sheet

  Scenario: Create the sheet
    Given the database is purged
    And the event "Group Event" is created
    And the user "group@example.net" is created
    And there is a group "Group" managed by this user
    And there is a sheet in this group with the title "Test"
    And I am logged with "group@example.net" on event "http://super-event.vimeet.proximum"
    And I am on this page "/fr/sheets-group/1"
    And I should see "Test"
    And I should not see "This is the new sheet"
    When I follow "group.home.link.create_sheet"
    And I should be on this page "/fr/sheets-group/1/sheet/create"
    And I fill in the following:
      | sheet_group_create_sheet_sheet | 0                     |
      | sheet_group_create_sheet_title | This is the new sheet |
    And I press "form.sheet_group_create_sheet.children.submit.label"
    Then I should be on this page "/fr/sheets-group/1"
    And I should see "flash.group.sheet.create.success"
    And I should see "This is the new sheet"

  Scenario: I can manage meeting in sheet group
    Given I am logged with "group@example.net" on event "http://super-event.vimeet.proximum"
    And I am on this page "/fr/sheets-group/1"
    When I follow "group.home.link.request"
    Then I should be on this page "/fr/sheets-group/1/requests/list"

  Scenario: I can see participants list
    Given I am logged with "group@example.net" on event "http://super-event.vimeet.proximum"
    And I am on this page "/fr/sheets-group/1"
    When I follow "group.home.link.participants"
    Then I should be on this page "/fr/sheets-group/1/participants/list"

  Scenario: I can update sheet participant
    Given I am logged with "group@example.net" on event "http://super-event.vimeet.proximum"
    And I am on this page "/fr/sheets-group/1"
    When I follow "group.home.link.participant_update"
    Then I should be on this page "/fr/sheets-group/1/participants/update"

