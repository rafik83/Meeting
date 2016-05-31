Feature: Change my mail
  I need to be able to change my account

  Background:
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml  |
      | UserWithActivateAccountTokenAndSheet.yml                       |
    And I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"

  Scenario: I can change my email
    When I go to this page "/fr/account/change_mail/azertyuiopqsdfghjklmwxcvbn"
    Then I should be on this page "/fr/"
    When I follow "event.link.see_my_sheet"
    Then the response status code should be 200

  Scenario: I can change my email full process
    When I go to this page "/fr/account/change-mail"
    Then I fill in the following:
      | form.change_mail.children.mail.label | truc@bidule.com |
    And I press "form.change_mail.children.submit.label"
    Then I should be on "/fr/"
    And the response status code should be 200
    And I should see "flash.change_mail.success"
    And the "change_mail_old" mail should be sent to "test@test.com"
    And the "change_mail_new" mail should be sent to "truc@bidule.com"
    And the "change_mail_new" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/account/change_mail/"
    Then I follow the "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/account/change_mail/" link in the "change_mail_new" mail
    And the response status code should be 200
    Then I should be on "/fr/"



