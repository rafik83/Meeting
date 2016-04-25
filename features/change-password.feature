Feature: Change password
  When I am logged, I need to be able to change my password

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Event.yml |
      | User.yml      |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"

  Scenario: Change the password successfully
    When I go to this page "/fr/change-password"
    And I fill in the following:
      |form.change_password_user.children.currentPassword.label               |p@ssw0rd     |
      |form.change_password_user.children.plainPassword.children.first.label  |new-p@ssw0rd |
      |form.change_password_user.children.plainPassword.children.second.label |new-p@ssw0rd |
    And I press "form.change_password_user.children.submit.label"
    And I should see "flash.change_password.success"

  Scenario: Change the password failed
    When I go to this page "/fr/change-password"
    And I fill in the following:
      |form.change_password_user.children.currentPassword.label               |whatever-wrong-password     |
      |form.change_password_user.children.plainPassword.children.first.label  |new-p@ssw0rd                |
      |form.change_password_user.children.plainPassword.children.second.label |new-p@ssw0rd                |
    And I press "form.change_password_user.children.submit.label"
    And I should see "validators.currentPassword"
