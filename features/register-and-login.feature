Feature: Register and login user
  I need to be able to register to an event and login to my account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml          |
      | template/Sheet.yml        |
      | template/Registration.yml |
      | app/Event.yml             |
      | Nomenclatures.yml         |
      | app/Type.yml              |

  Scenario: Register an user
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I check the "Exposant" radio
    And I press "common.next"
    And the response status code should be 200
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    And the response status code should be 200
    And I fill in "register_new_user_password_first" with "p@ssw0rd"
    And I fill in "register_new_user_password_second" with "p@ssw0rd"
    And I press "common.next"
    And the response status code should be 200

  Scenario: User already exists
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I check the "Exposant" radio
    And I press "common.next"
    And the response status code should be 200
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then I should be on this page "/fr/login-second-step"
    And I should see "flash.event.register.already_known.message"

  Scenario: Login successful
    When the fixtures "User.yml" are loaded
    And I go to this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    And the response status code should be 200
    And I should see "test@test.com"
    And I fill in "login_password" with "p@ssw0rd"
    And I press "login.connect"
    And the response status code should be 200
    Then I should be on this page "/fr/"

  Scenario: Login failed
    When the fixtures "User.yml" are loaded
    And I go to this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    And the response status code should be 200
    And I should see "test@test.com"
    And I fill in "login_password" with "wrong-p@ssw0rd"
    And I press "login.connect"
    And the response status code should be 200
    And I should see "Bad credentials."
