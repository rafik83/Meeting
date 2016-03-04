Feature: Login admin
  I need to be able to login to my admin account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Event.yml |
      | Admin.yml     |

  Scenario: Login successful
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin"
    And I press "form.login.children.submit.label"
    Then I should be on "http://vimeet.proximum.dev/app_test.php/admin/event"
    And the response status code should be 200
    And I should see "admin.login.logged_as"

  Scenario: Login failed
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "whatever-wrong-password"
    And I press "form.login.children.submit.label"
    Then I should be on "http://vimeet.proximum.dev/app_test.php/admin/login"
    And the response status code should be 200
    And I should see "Invalid credentials."
