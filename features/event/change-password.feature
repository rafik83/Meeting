@event
@account
Feature: Change password
When I am logged, I need to be able to change my password

  Scenario: Change the password successfully
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
    And I am logged with "test_carnot@proximum.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/4"
    When I go to this page "/fr/sheet/4/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | password     |
      | form.change_password_user.children.plainPassword.children.first.label  | new-p@ssw0rd |
      | form.change_password_user.children.plainPassword.children.second.label | new-p@ssw0rd |
    And I press "common.validate"
    Then I should see "flash.change_password.success"

  Scenario: Change the password failed
    Given I am logged with "test_carnot@proximum.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/4"
    When I go to this page "/fr/sheet/4/change-password"
    And I fill in the following:
      | form.change_password_user.children.currentPassword.label               | whatever-wrong-password |
      | form.change_password_user.children.plainPassword.children.first.label  | new-p@ssw0rd            |
      | form.change_password_user.children.plainPassword.children.second.label | new-p@ssw0rd            |
    And I press "common.validate"
    Then I should see "validators.currentPassword"
