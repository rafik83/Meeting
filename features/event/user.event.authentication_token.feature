@user @event @authentication_token
  Feature: User authentication via token
    As a user I can access to my sheet

  Scenario: I access to my sheet with an authentication token
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is an authentication token "1337ABCD2018" for this user on this event
    When I go to this page "http://super-event.vimeet.proximum/login?token=1337ABCD2018"
    Then I should be on this page "/fr/account/sheet/1/participant/1/profile"

  Scenario: I can access to my sheet with an authentication token without password
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar@example.net" with empty password is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is an authentication token "1337ABCD2018" for this user on this event
    When I go to this page "http://super-event.vimeet.proximum/login?token=1337ABCD2018"
    Then I should be on this page "/fr/account/sheet/1/participant/1/profile"

  Scenario: I attempt to login with a bad token
    Given the database is purged
    And the event "Fondation PSG" is created
    And the user "neymar@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    When I go to this page "http://super-event.vimeet.proximum/login?token=abc"
    Then I should be on this page "/fr/login"
