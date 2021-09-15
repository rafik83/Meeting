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
    When I go to this page "http://super-event.vimeet.proximum/fr/confirm/agenda/mytoken"
    Then I should see "user_event.confirm.agenda.success"
    And I should not see "form.send_code.children.phone.label"
    # If I re go to this page
    When I go to this page "http://super-event.vimeet.proximum/fr/confirm/agenda/mytoken"
    Then I should see "user_event.confirm.agenda.already_confirmed"

  Scenario: I can confirm my mobile phone
    Given the database is purged
    And the event "Concert des tokens userevent" is created
    And the user "concert-tokens-userevent@example.net" is created
    And there is a type in this event
    And there is a sheet of this type
    And there is a participant for this sheet and this user
    And there is a confirmation agenda token "mytoken" for this user on this event
    And a tip "Confirm your mobile phone" is enabled on confirmation phone context for this type
    When I go to this page "http://super-event.vimeet.proximum/fr/confirm/agenda/mytoken"
    Then I should see "user_event.confirm.agenda.success"
    And I should see "Confirm your mobile phone"
    And I should see "form.send_code.children.phone.label"
    And I fill in the following:
      | form.send_code.children.phone.label | +33112233445566 |
    And I check "form.send_code.children.accepted.label"
    And I press "form.send_code.children.submit.label"
    Then a SMS should be sent to "+33112233445566" with content "user.phone.confirmationCode.message"
