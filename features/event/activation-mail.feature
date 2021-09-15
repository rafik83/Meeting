@event @activation-mail

Feature: I can define my password if I'm an imported participant and a fresh user

  Scenario: I receive an email if I'm an imported participant and a user with empty password
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar-nopassword@example.net" with empty password is created
    And there is a sheet
    And there is an imported participant for this sheet and this user
    When I go to this page "http://super-event.vimeet.proximum/fr/login"
    And I fill in "email_email" with "neymar-nopassword@example.net"
    And I press "common.next"
    Then I should be on this url "/fr/activation-mail"
    And the "user.account_activated" mail should be sent to "neymar-nopassword@example.net" from "no-reply@super-event.vimeet.proximum"
    And the "user.account_activated" mail should contain the link "http://super-event.vimeet.proximum/fr/activate/"
    When I go to this page "http://super-event.vimeet.proximum/fr"
    And I check radio "type_choice_type_0"
    And I press "common.next"
    And I should be on this url "/fr/register/1"
    And I fill in "email_email" with "neymar-nopassword@example.net"
    And I press "common.next"
    And I should be on this url "/fr/activation-mail"

  Scenario: As an imported participant, if my password is already defined, I can type my password
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar-withpassword@example.net" is created
    And there is a sheet
    And there is an imported participant for this sheet and this user
    When I go to this page "http://super-event.vimeet.proximum/fr/login"
    And I fill in "email_email" with "neymar-withpassword@example.net"
    And I press "common.next"
    Then I should be on this url "http://super-event.vimeet.proximum/fr/login-second-step"
    And this page "http://super-event.vimeet.proximum/fr/activation-mail" returns 403

  Scenario: As a not-imported participant, if my password is already defined, I can type my password
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar-withpassword@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    When I go to this page "http://super-event.vimeet.proximum/fr/login"
    And I fill in "email_email" with "neymar-withpassword@example.net"
    And I press "common.next"
    Then I should be on this url "http://super-event.vimeet.proximum/fr/login-second-step"
    And this page "http://super-event.vimeet.proximum/fr/activation-mail" returns 403
