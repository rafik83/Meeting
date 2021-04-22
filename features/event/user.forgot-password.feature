@event @account @mail
Feature: Forgot Password
  I need to be able to change my password if I forgot it

  Scenario: I can not request a token for a non-existent account
    Given the database is purged
    And the event "RDV Carnot" is created
    And the domain for this event is "rdv-carnot-2016.vimeet.proximum"
    And the user "user_1@proximum.com" is created
    And there is a sheet
    And there is a participant for this sheet and this user

    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/fr/forgotten_password"
    And I fill in "form.forgotten_password.children.email.label" with "not-known-user@example.net"
    And I press "form.forgotten_password.children.submit.label"
    Then the response status code should be 200

  Scenario: I can request a token for an existant account and change the password
    When I go to "http://rdv-carnot-2016.vimeet.proximum/fr/forgotten_password"
    And I fill in "form.forgotten_password.children.email.label" with "user_1@proximum.com"
    And I press "form.forgotten_password.children.submit.label"
    And I should be on this page "http://rdv-carnot-2016.vimeet.proximum/fr/forgotten_password/confirm"
    And the "user.password_reset" mail should be sent to "user_1@proximum.com" from "no-reply@rdv-carnot-2016.vimeet.proximum"
    And the "user.password_reset" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum/fr/reset_password/"
    And I follow the "http://rdv-carnot-2016.vimeet.proximum/fr/reset_password/" link in the "user.password_reset" mail
    And the response status code should be 200
    And I should see "new_password.title"
    And I fill in the following:
      |form.new_password.children.password.children.first.label  | newpassword7A |
      |form.new_password.children.password.children.second.label | newpassword7A |
    And I press "common.validate"
    Then I should be on "/fr/account/sheet/1/participant/1/profile"
    And the response status code should be 200
    And I should see "flash.new_password.success"
