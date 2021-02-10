@admin @mail

Feature: Forgot Password Admin
  I need to be able to change my password if I forgot it as an Admin

  Background: Re-init the database and load the fixtures
    Given the database is purged
    And the super admin "test@test.com" is created

  Scenario: I can request a token for a non-existent account but I ignore if it succeeded for security reason
    When I go to this page "/fr/login"
    And I follow "login.forgotPassword"
    And I should be on this page "/fr/forgot-password"
    And I fill in "form.forgotten_password.children.email.label" with "test-impossible@test.com"
    And I press "form.forgotten_password.children.submit.label"
    Then the response status code should be 200
    And I should be on this page "/fr/login"
    And I should see "flash.admin.reset_password_token.success"

  Scenario: I can request a token for an existent account and change the password
    When I go to this page "/fr/login"
    And I follow "login.forgotPassword"
    And I should be on this page "/fr/forgot-password"
    And I fill in "form.forgotten_password.children.email.label" with "test@test.com"
    And I press "form.forgotten_password.children.submit.label"
    And I should be on this page "/fr/login"
    And I should see "flash.admin.reset_password_token.success"
    And the "admin.password_reset" mail should be sent to "test@test.com" from "vimeet"
    And the "admin.password_reset" mail should contain the link "http://admin.vimeet.proximum/fr/reset-password/"
    And I follow the "http://admin.vimeet.proximum/fr/reset-password/" link in the "admin.password_reset" mail
    And the response status code should be 200
    And I should see "new_password.title"
    And I fill in the following:
      | form.new_password.children.password.children.first.label  | testTEST1234 |
      | form.new_password.children.password.children.second.label | testTEST1234 |
    And I press "form.new_password.children.submit.label"
    Then I should be on this page "/fr/event"
    And I should see "flash.new_password.success"
