Feature: Change password
When I am logged, I need to be able to change my password

  Scenario: Change the password successfully
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | User.yml                                                                 |
    And I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I go to this page "/fr/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | p@ssw0rd     |
      | form.change_password_user.children.plainPassword.children.first.label  | new-p@ssw0rd |
      | form.change_password_user.children.plainPassword.children.second.label | new-p@ssw0rd |
    And I press "form.change_password_user.children.submit.label"
    Then I should see "flash.change_password.success"

  Scenario: Change the password failed
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I go to this page "/fr/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | whatever-wrong-password |
      | form.change_password_user.children.plainPassword.children.first.label  | new-p@ssw0rd            |
      | form.change_password_user.children.plainPassword.children.second.label | new-p@ssw0rd            |
    And I press "form.change_password_user.children.submit.label"
    Then I should see "validators.currentPassword"
