@admin

Feature: Login admin
  I need to be able to login to my admin account

  Scenario: Login successful
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | Admins.yml                                                               |
    When I go to this page "/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/fr/event"
    And I should see "admin.login.logged_as"

  Scenario: Login failed
    When I go to this page "/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "whatever-wrong-password"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/fr/login"
    And I should see "Invalid credentials."

  Scenario: Login failed for deactivated admin
    When I go to this page "/fr/login"
    And I fill in "form.login.children.username.label" with "test3@test.com"
    And I fill in "form.login.children.password.label" with "vimeet_admin"
    And I press "form.login.children.submit.label"
    Then I should be on this page "/fr/login"
    And I should see "An authentication exception occurred."

