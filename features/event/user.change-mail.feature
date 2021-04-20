@event @account @mail
Feature: Change my mail
  I need to be able to change my account

  Background:
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And this user has a token "azertyuiopqsdfghjklmwxcvbn" to change his email to "user_asddays_2@proximum.com"

  Scenario: I can change my email
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr/account/change_mail/azertyuiopqsdfghjklmwxcvbn"
    Then I should be on this page "/fr/account/sheet/1/participant/1/profile"

  Scenario: I can change my email full process
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr/account/sheet/1/change-mail"
    Then I fill in the following:
      | form.change_mail.children.mail.label | truc@bidule.com |
      | form.change_mail.children.password.label | p@ssw0rd |
    And I press "common.validate"
    Then I should be on "/fr/account/sheet/1/participant/1/profile"
    And I should see "flash.change_mail.success"
    And the "change_mail_old" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays.vimeet.proximum"
    And the "change_mail_new" mail should be sent to "truc@bidule.com" from "no-reply@asddays.vimeet.proximum"
    And the "change_mail_new" mail should contain the link "http://asddays.vimeet.proximum/fr/account/change_mail/"
    Then I follow the "http://asddays.vimeet.proximum/fr/account/change_mail/" link in the "change_mail_new" mail
    And the response status code should be 200
    Then I should be on "/fr/account/sheet/1/participant/1/profile"

  Scenario: I can not access the url when I am not logged in
    When I go to "/fr/account/sheet/1/change-mail"
    Then I should be on this page "/fr/login"
