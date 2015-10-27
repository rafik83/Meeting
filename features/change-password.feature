Feature: Change password
  When I am logged, I need to be able to change my password

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml |
      | User.yml                                               |

  Scenario: Change the password successfully
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in the following:
      |form.login.children.username.label |test@test.com |
      |form.login.children.password.label |p@ssw0rd      |
    And I press "form.login.children.submit.label"
    And I should see "login.logged_as"
    Then I follow "change_password.link"
    And I fill in the following:
      |form.change_password.children.currentPassword.label               |p@ssw0rd     |
      |form.change_password.children.plainPassword.children.first.label  |new-p@ssw0rd |
      |form.change_password.children.plainPassword.children.second.label |new-p@ssw0rd |
    And I press "form.change_password.children.submit.label"
    And I should see "flash.change_password.success"

  Scenario: Change the password failed
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in the following:
      |form.login.children.username.label |test@test.com |
      |form.login.children.password.label |p@ssw0rd      |
    And I press "form.login.children.submit.label"
    And I should see "login.logged_as"
    Then I follow "change_password.link"
    And I fill in the following:
      |form.change_password.children.currentPassword.label               |whatever-wrong-password     |
      |form.change_password.children.plainPassword.children.first.label  |new-p@ssw0rd                |
      |form.change_password.children.plainPassword.children.second.label |new-p@ssw0rd                |
    And I press "form.change_password.children.submit.label"
    And I should see "validators.currentPassword"
