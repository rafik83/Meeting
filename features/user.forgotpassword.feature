Feature: Forgot Password
  I need to be able to change my password if I forgot it

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | User.yml                                                  |
      | Sheet.yml                                                 |
      | OneSheetSeveralParticipants.yml                           |

  Scenario: I can not request a token for a non-existent account
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I follow "login.forgotPassword"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/forgotten_password"
    And the response status code should be 200
    And I fill in "form.forgotten_password.children.email.label" with "test-impossible@test.com"
    And I press "form.forgotten_password.children.submit.label"
    Then the response status code should be 200
    And I should see "validators.emailDoesNotExist"

  Scenario: I can request a token for an existent account and change the password
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I follow "login.forgotPassword"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/forgotten_password"
    And the response status code should be 200
    And I fill in "form.forgotten_password.children.email.label" with "test@test.com"
    And I press "form.forgotten_password.children.submit.label"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I should see "flash.reset_password_token.success"
    And the "forgot_password" mail should be sent to "test@test.com"
    And the "forgot_password" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/reset_password/"
    And I follow the "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/reset_password/" link in the "forgot_password" mail
    And the response status code should be 200
    And I should see "new_password.title"
    And I fill in the following:
      |form.new_password.children.password.children.first.label  | testtest |
      |form.new_password.children.password.children.second.label | testtest |
    And I press "form.new_password.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    And I should see "flash.new_password.success"
