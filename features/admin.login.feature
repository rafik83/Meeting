Feature: Login admin
  I need to be able to login to my admin account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Event.yml |
      | Admins.yml     |

  Scenario: Login successful
    When I go to this page "/admin/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/admin/fr/event"
    And I should see "admin.login.logged_as"

  Scenario: Login failed
    When I go to this page "/admin/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "whatever-wrong-password"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/admin/fr/login"
    And I should see "Invalid credentials."

  Scenario: Login failed for deactivated admin
    When I go to this page "/admin/fr/login"
    And I fill in "form.login.children.username.label" with "test3@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin"
    And I press "form.login.children.submit.label"
    Then I should be on this page "admin/fr/login"
    And I should see "Account is disabled."

