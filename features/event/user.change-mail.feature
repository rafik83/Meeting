@event @account @mail
Feature: Change my mail
  I need to be able to change my account

  Background:
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml         |
      | UserWithActivateAccountTokenAndSheet.yml                              |

  Scenario: I can change my email
    When I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr/account/change_mail/azertyuiopqsdfghjklmwxcvbn"
    Then I should be on this page "/fr/participant/1/step/1"

  Scenario: I can change my email full process
    When I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr/account/sheet/1/change-mail"
    Then I fill in the following:
      | form.change_mail.children.mail.label | truc@bidule.com |
      | form.change_mail.children.password.label | p@ssw0rd |
    And I press "common.validate"
    Then I should be on "/fr/participant/1/step/1"
    And the response status code should be 200
    And I should see "flash.change_mail.success"
    And the "change_mail_old" mail should be sent to "test@test.com" from "no-reply@rdv-carnot-2016.vimeet.proximum"
    And the "change_mail_new" mail should be sent to "truc@bidule.com" from "no-reply@rdv-carnot-2016.vimeet.proximum"
    And the "change_mail_new" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum/fr/account/change_mail/"
    Then I follow the "http://rdv-carnot-2016.vimeet.proximum/fr/account/change_mail/" link in the "change_mail_new" mail
    And the response status code should be 200
    Then I should be on "/fr/participant/1/step/1"

  Scenario: I can not access the url when I am not logged in
    When I go to "/fr/account/sheet/1/change-mail"
    Then I should be on this page "/fr/login"
