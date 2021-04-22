@event
@account
Feature: Change password
When I am logged, I need to be able to change my password

  Scenario: Change the password successfully
    Given the database is purged
    And the event "RDV Carnot 2019" is created
    And the domain for this event is "rdvcarnot.vimeet.proximum"
    And the user "test_carnot@proximum.com" is created
    And there is a sheet with the title "Test Carnot"
    And there is a participant for this sheet and this user
    And I am logged with "test_carnot@proximum.com" on front
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd |
      | form.change_password_user.children.plainPassword.children.first.label  | short    |
      | form.change_password_user.children.plainPassword.children.second.label | short    |
    And I press "common.validate"
    Then I should see "validators.password.min"
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd       |
      | form.change_password_user.children.plainPassword.children.first.label  | Missing-number |
      | form.change_password_user.children.plainPassword.children.second.label | Missing-number |
    And I press "common.validate"
    Then I should see "validators.password.atLeastOneNumber"
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd          |
      | form.change_password_user.children.plainPassword.children.first.label  | MISSING-LOWERCASE |
      | form.change_password_user.children.plainPassword.children.second.label | MISSING-LOWERCASE |
    And I press "common.validate"
    Then I should see "validators.password.atLeastOneLowercase"
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd          |
      | form.change_password_user.children.plainPassword.children.first.label  | missing-uppercase |
      | form.change_password_user.children.plainPassword.children.second.label | missing-uppercase |
    And I press "common.validate"
    Then I should see "validators.password.atLeastOneUppercase"
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd      |
      | form.change_password_user.children.plainPassword.children.first.label  | Good1Password |
      | form.change_password_user.children.plainPassword.children.second.label | Good1Password |
    And I press "common.validate"
    Then I should see "flash.change_password.success"

  Scenario: Change the password failed
    Given there is an event with domain "rdvcarnot.vimeet.proximum"
    Given I am logged with "test_carnot@proximum.com" on front
    And I go to this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | whatever-wrong-password |
      | form.change_password_user.children.plainPassword.children.first.label  | new-p@ssw0rd            |
      | form.change_password_user.children.plainPassword.children.second.label | new-p@ssw0rd            |
    And I press "common.validate"
    Then I should see "validators.currentPassword"
