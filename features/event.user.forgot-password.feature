Feature: Forgot Password
  I need to be able to change my password if I forgot it

  Scenario: I can not request a token for a non-existent account
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml  |
      | UserWithActivateAccountTokenAndSheet.yml                       |
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/forgotten_password"
    And I fill in "form.forgotten_password.children.email.label" with "not-known-user@example.net"
    And I press "form.forgotten_password.children.submit.label"
    Then the response status code should be 200
    And I should see "validators.emailDoesNotExist"

  Scenario: I can request a token for an existent account and change the password
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/forgotten_password"
    And I fill in "form.forgotten_password.children.email.label" with "test@test.com"
    And I press "form.forgotten_password.children.submit.label"
    And I should be on this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I should see "flash.reset_password_token.success"
    And the "user_forgot_password" mail should be sent to "test@test.com"
    And the "user_forgot_password" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/reset_password/"
    And I follow the "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/reset_password/" link in the "user_forgot_password" mail
    And the response status code should be 200
    And I should see "new_password.title"
    And I fill in the following:
      |form.new_password.children.password.children.first.label  | newpassword |
      |form.new_password.children.password.children.second.label | newpassword |
    And I press "form.new_password.children.submit.label"
    Then I should be on "/fr/"
    And the response status code should be 200
    And I should see "flash.new_password.success"
