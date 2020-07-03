@admin

Feature: Admin Account
  I need to be able to manage the Admin account

  Scenario: I can change my password
    Given the database is purged
    And the super admin "test@test.com" is created
    And I am logged with "test@test.com" on admin
    When I go to this page "/fr/event"
    And I follow "admin.account.link"
    Then I should be on this page "/fr/account"
    And I follow "admin.account.content.password"
    Then I should be on this page "/fr/account/password"
    And I fill in the following:
      | form.change_password_admin.children.currentPassword.label               | Vimeet_admin1        |
      | form.change_password_admin.children.plainPassword.children.first.label  | Vimeet_admin_change1 |
      | form.change_password_admin.children.plainPassword.children.second.label | Vimeet_admin_change1 |
    Then I press "form.change_password_admin.children.submit.label"
    And I should be on this page "/fr/account"
    And I should see "flash.admin.change_password.success"
    Then I follow "logout.link"
    And I should be on this page "/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "Vimeet_admin_change1"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/fr/event"
    And I should see "admin.login.logged_as"
