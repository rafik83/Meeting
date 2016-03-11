Feature: Operator Activate Account
  I need to be able to activate my account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                  |
      | app/Event.yml                     |
      | AdminWithActivateAccountToken.yml |

  Scenario: I can activate my account
    When I go to this page "http://vimeet.proximum.dev/app_test.php/admin/activate-account/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I fill in the following:
      | form.admin_activate_account_password.children.password.children.first.label  | tructruc |
      | form.admin_activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.admin_activate_account_password.children.submit.label"
    Then I should be on this page "http://vimeet.proximum.dev/app_test.php/admin/event"
    And I should see "flash.admin.activate_account.success"
    And I should see "Les rendez-vous CARNOT 2016"

  Scenario: I can add an operator and activate the account
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/event"
    Then I follow "admin.operator.create.link"
    Then I should be on this page "/admin/operator/create"
    Then I fill in the following:
      | form.create_operator.children.email.label     | toto@toto.fr |
      | form.create_operator.children.lastname.label  | Toto         |
      | form.create_operator.children.firstname.label | Tata         |
    And I press "form.create_operator.children.submit.label"
    And the "admin_activate_account" mail should be sent to "toto@toto.fr"
    And the "admin_activate_account" mail should contain the link "http://vimeet.proximum.dev/app_test.php/admin/activate-account"
    Then I follow the "http://vimeet.proximum.dev/app_test.php/admin/activate-account" link in the "admin_activate_account" mail
    And the response status code should be 200
    Then I fill in the following:
      | form.admin_activate_account_password.children.password.children.first.label  | tructruc |
      | form.admin_activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.admin_activate_account_password.children.submit.label"
    Then I should be on this page "/admin/event"
    And I should see "flash.admin.activate_account.success"
    And I should see "Les rendez-vous CARNOT 2016"
