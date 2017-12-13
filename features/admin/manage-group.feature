@admin @group

Feature: Manage Group
  As an Admin I need to be able to manage group of sheet

  Scenario: I can see group list
    Given the database is purged
    And the event "Awsm Event" is created
    And the user "awsm@example.com" is created
    And there is a group "Awsm Group" managed by this user
    And I am logged as admin
    When I go to this page "/fr/event/1/sheets-group/list"
    Then I should see "Awsm Group"
    And I should see "awsm@example.com"

  Scenario: I can create a group
  As an admin I need to be able to create a group from an user email
    Given I am logged as admin
    And the event "Multisheet event" is created
    And the user "multisheet@example.com" is created
    And I am on this page "/fr/event/1/sheets-group/list"
    When I follow "admin.sheets_group.create"
    Then I should be on this page "/fr/event/1/sheets-group/pre-create"
    When I fill in the following:
      | search_user_sheets_group_email | multisheet@example.com |
    And I press "search_user_sheets_group_submit"
    Then I should be on this page "/fr/event/1/sheets-group/2/create"
    And the response should contain "multisheet@example.com"
    When I fill in the following:
      | create_sheets_group_title | "Group title" |
    And I press "create_sheets_group_submit"
    Then I should be on this page "/fr/event/1/sheets-group/list"
    And I should see "Group title"
    And I should see "flash.admin.group.create.success"
    And the "sheet.group.created" mail should be sent to "multisheet@example.com" from "no-reply@super-event.vimeet.proximum"
    And the "sheet.group.created" mail should be sent in bcc to "team-project@example.net" from "no-reply@super-event.vimeet.proximum"

  Scenario: I should see an error if user doesn't exist
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheets-group/list"
    When I follow "admin.sheets_group.create"
    Then I should be on this page "/fr/event/1/sheets-group/pre-create"
    When I fill in the following:
      | search_user_sheets_group_email | inexistant_user@example.com |
    And I press "search_user_sheets_group_submit"
    Then I should see "validators.group.email_not_found"

  Scenario: I should see an error if user is already group manager
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheets-group/list"
    When I follow "admin.sheets_group.create"
    Then I should be on this page "/fr/event/1/sheets-group/pre-create"
    When I fill in the following:
      | search_user_sheets_group_email | multisheet@example.com |
    And I press "search_user_sheets_group_submit"
    Then I should see "validators.group.user_not_allowed_to_manage"

  Scenario: I can update a sheet group
    Given I am logged as admin
    And the user "user_not_mananing_group@vimeet.com" is created
    And I am on this page "/fr/event/1/sheets-group/list"
    And I should see "Awsm Group"
    When I follow "admin.sheets_group.update"
    Then I should be on this page "/fr/event/1/sheets-group/1/update"
    And the "update_sheets_group_title" field should contain "Awsm Group"
    And the "update_sheets_group_email" field should contain "awsm@example.com"
    When I fill in the following:
      | update_sheets_group_title | Group title Two                    |
      | update_sheets_group_email | user_not_mananing_group@vimeet.com |
    And I press "update_sheets_group_submit"
    Then I should be on this page "/fr/event/1/sheets-group/list"
    And I should see "flash.admin.group.update.success"
    And I should see "Group title two"
    And I should not see "Awsm Group"
    And the "sheet.group.created" mail should be sent to "user_not_mananing_group@vimeet.com" from "no-reply@super-event.vimeet.proximum"
    And the "sheet.group.created" mail should be sent in bcc to "team-project@example.net" from "no-reply@super-event.vimeet.proximum"

  Scenario: On update, I should see an error if user doesn't exist
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheets-group/list"
    When I follow "admin.sheets_group.update"
    Then I should be on this page "/fr/event/1/sheets-group/1/update"
    When I fill in the following:
      | update_sheets_group_email | unknown_user@example.com |
    And I press "update_sheets_group_submit"
    Then I should see "validators.group.email_not_found"

  Scenario: On update, I should see an error if user is already group manager
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheets-group/list"
    When I go to "/fr/event/1/sheets-group/1/update"
    And I fill in the following:
      | update_sheets_group_email | multisheet@example.com |
    And I press "update_sheets_group_submit"
    Then I should see "validators.group.user_not_allowed_to_manage"
