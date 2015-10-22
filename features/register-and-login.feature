Feature: Register and login user
  I need to be able to register to an event and login to my account

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Event.yml" are loaded

  Scenario: Register an user
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I follow "Exposant"
    And I fill in "form.register.children.email.label" with "test@test.com"
    And I fill in "form.register.children.password.children.first.label" with "p@ssw0rd"
    And I fill in "form.register.children.password.children.second.label" with "p@ssw0rd"
    And I press "form.register.children.submit.label"
    Then I should see "flash.event.register.success"

  Scenario: User already exists
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I follow "Exposant"
    And I fill in "form.register.children.email.label" with "test@test.com"
    And I fill in "form.register.children.password.children.first.label" with "p@ssw0rd"
    And I fill in "form.register.children.password.children.second.label" with "p@ssw0rd"
    And I press "form.register.children.submit.label"
    Then I should see "messages.register.email_already_exists"

  Scenario: Login successful
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I should see "login.logged_as"

  Scenario: Login failed
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "whatever-wrong-password"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I should see "Invalid credentials."
