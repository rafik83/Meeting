@event @agenda @token
  Feature: Confirmed action via token
    As a user I can confirm action via token

  Scenario: I can confirm my agenda
    Given the database is purged
    And the event "Concert des tokens userevent" is created
    And the user "concert-tokens-userevent@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is a confirmation agenda token "mytoken" for this user on this event
    When I go to this page "http://super-event.vimeet.proximum.dev/app_test.php/fr/confirm/agenda/mytoken"
    Then I should see "user_event.confirm.agenda.success"
    # If I re go to this page
    When I go to this page "http://super-event.vimeet.proximum.dev/app_test.php/fr/confirm/agenda/mytoken"
    Then I should see "user_event.confirm.agenda.already_confirmed"
