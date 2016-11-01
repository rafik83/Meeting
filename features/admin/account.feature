@admin

Feature: Admin Account
  I need to be able to manage the Admin account

  Scenario: I can change my password
    Given the database is empty
    And the following fixtures files are loaded:
      | Admin.yml        |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event"
    And I follow "admin.account.link"
    Then I should be on this page "/admin/fr/account"
    And I follow "admin.account.content.password"
    Then I should be on this page "/admin/fr/account/password"
    And I fill in the following:
      | form.change_password_admin.children.currentPassword.label               | vimeet_admin        |
      | form.change_password_admin.children.plainPassword.children.first.label  | vimeet_admin_change |
      | form.change_password_admin.children.plainPassword.children.second.label | vimeet_admin_change |
    Then I press "form.change_password_admin.children.submit.label"
    And I should be on this page "/admin/fr/account"
    And I should see "flash.admin.change_password.success"
    Then I follow "logout.link"
    And I should be on this page "/admin/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin_change"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/admin/fr/event"
    And I should see "admin.login.logged_as"
