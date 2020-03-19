@event @package
Feature: User authentication security

  Scenario: My account is temporarily disabled after 5 wrong attempts
    Given the database is purged
    And the event "ForumPHP" is created
    And the user "user@example.net" is created
    When I go to this page "http://super-event.vimeet.proximum/fr/login"
    And I fill in "email_email" with "user@example.net"
    And I press "common.next"
    Then I should be on this page "/fr/login-second-step"
    And I should see "user@example.net"
    When I fill in "login_password" with "Wrong-Password"
    And I press "login.connect"
    Then I should be on this page "/fr/login-second-step"
    And I should see "Bad credentials"
    And I should see "authentication.remaining_attempt"
    When I fill in "login_password" with "Wrong-Password"
    And I press "login.connect"
    Then I should be on this page "/fr/login-second-step"
    And I should see "Bad credentials"
    And I should see "authentication.remaining_attempt"
    When I fill in "login_password" with "Wrong-Password"
    And I press "login.connect"
    Then I should be on this page "/fr/login-second-step"
    And I should see "Bad credentials"
    And I should see "authentication.remaining_attempt"
    When I fill in "login_password" with "Wrong-Password"
    And I press "login.connect"
    Then I should be on this page "/fr/login-second-step"
    And I should see "Bad credentials"
    And I should see "authentication.remaining_attempt"
    When I fill in "login_password" with "Wrong-Password"
    And I press "login.connect"
    Then I should be on this page "/fr/login-second-step"
    And I should see "authentication.account_temporarily_disabled"
    And the "user.account_temporarily_disabled" mail should be sent to "user@example.net" from "no-reply@super-event.vimeet.proximum"
