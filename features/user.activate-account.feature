Feature: Activate Account
  I need to be able to activate my account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                         |
      | app/Event.yml                            |
      | app/Type.yml                             |
      | UserWithActivateAccountTokenAndSheet.yml |

  Scenario: I can activate my account
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/activate/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I fill in the following:
      | form.activate_account_password.children.password.children.first.label  | tructruc |
      | form.activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.activate_account_password.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/update_participant/2"
    And the response status code should be 200
    And I should see "flash.activate_account.success"


