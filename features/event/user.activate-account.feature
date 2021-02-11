@event
@account
Feature: Activate Account
  I need to be able to activate my account

  Scenario: I can activate my account
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And this user has an activate token "azertyuiopqsdfghjklmwxcvbn" for this sheet

    When I go to this page "http://asddays.vimeet.proximum/fr/activate/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I fill in the following:
      | form.activate_account_password.children.password.children.first.label  | P1ssW0rd |
      | form.activate_account_password.children.password.children.second.label | P1ssW0rd |
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1/profile"
