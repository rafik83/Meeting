@event
@registration
Feature: Register and login user
  I need to be able to register to an event and login to my account

  Scenario: Register a user in 2 steps
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And there is a turnover nomenclature
    And there is a registration template
    # We need several types, to display the step where user is asked to choose
    And there is a type "Fournisseur" in this event
    And there is a package "Package for participant" for this event
    And this package is assigned to this type
    And there is a plan called "Formule Jumbo" with a price of "567"
    And this plan is assigned to this package
    And there is a type "Exposant" in this event

    When I go to this page "http://asddays.vimeet.proximum/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200

    When I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200

    When I fill in "register_new_user_password_first" with "P1ssw0rd"
    And I fill in "register_new_user_password_second" with "P1ssw0rd"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "Profil"
    And I should see "register.step"
    And I should see "1/2"

  Scenario: Register a user in one step
    Given there is an event with domain "asddays.vimeet.proximum"
    And there is a type "Investisseur" in this event
    And there is a single step registration template
    And this type has this registration template
    When I go to this page "http://asddays.vimeet.proximum/fr/"
    And I check the "Investisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "user@example.net"
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "register_new_user_password_first" with "My p1ssw0rd"
    And I fill in "register_new_user_password_second" with "My p1ssw0rd"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "Profil"
    And I should not see "register.step"

  Scenario: User already exists
    When I go to this page "http://asddays.vimeet.proximum/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then I should be on this page "/fr/login-second-step"
    And I should see "flash.event.register.already_known.message"

  Scenario: Login successful
    When I go to this page "http://asddays.vimeet.proximum/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "test@test.com"
    When I fill in "login_password" with "P1ssw0rd"
    And I press "login.connect"
    Then the response status code should be 200
    And I should be on this page "/fr/"

  Scenario: Login failed
    And I go to this page "http://asddays.vimeet.proximum/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "test@test.com"
    When I fill in "login_password" with "wrong-p@ssw0rd"
    And I press "login.connect"
    Then the response status code should be 200
    And I should see "Bad credentials."

  Scenario: Fill a participant profile
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "test@test.com" on front
    When I go to this page "/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then I should be on this page "/fr/participate/1"
    When I fill in the following:
      | Prénom | Paul   |
      | Nom    | Dupont |
    Then I check the "gender.man" radio
    And I select ">1M€" from "block[adc97e8d][item][first]"
    And I press "common.next"
    Then the response status code should be 200

  Scenario: Redirect on registration unfinished step
    When I go to this page "http://asddays.vimeet.proximum/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "test_unfinished_step@test.com"
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "register_new_user_password_first" with "P1ssw0rd"
    And I fill in "register_new_user_password_second" with "P1ssw0rd"
    And I press "common.next"
    Then the response status code should be 200
    Then I should see "Profil"
    When I fill in the following:
      | Titre                     | Aanera   |
      | Prénom                    | Paul     |
      | Nom                       | Dupont   |
      | Nom (Société / Organisme) | Fairness |
    Then I check the "gender.man" radio
    And I select ">1M€" from "block[adc97e8d][item][first]"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "register.step"
    And I should see "2/2"
    When I press "register.finalize"
    Then I should be on this page "/fr/sheet/1"
    When I follow "navigation.links.notification"
    Then I should be on this page "/fr/sheet/1/notification"
    And I should see "notification.list.title.label"
    And I should see "notification.package.noOrder"
    And I should see "notification.label.required"
    And I should see "notification.category.package.label"
