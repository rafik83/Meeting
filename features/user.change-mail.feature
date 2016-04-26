Feature: Change my mail
  I need to be able to change my account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                         |
      | app/Event.yml                            |
      | app/Type.yml                             |
      | UserWithActivateAccountTokenAndSheet.yml |

  Scenario: I can change my email
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    When I go to "/fr/account/change_mail/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I should be on "/fr/"
    And the response status code should be 200
    Then I follow "event.link.see_my_sheet"
    And the response status code should be 200

  Scenario: I can change my email full process
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    When I go to this page "/fr/account"
    And the response status code should be 200
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



