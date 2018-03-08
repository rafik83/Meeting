@event
@registration
Feature: Register and login user
  I need to be able to register to an event and login to my account

  Scenario: Register an user in 3 steps
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "register_new_user_password_first" with "p@ssw0rd"
    And I fill in "register_new_user_password_second" with "p@ssw0rd"
    And I press "common.next"
    Then the response status code should be 200
    Then I should see "Profil"
    And I should see "register.step"
    And I should see "1/3"

  Scenario: Register an user in one step
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/"
    And I check the "Structure de recherche" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "user@example.net"
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "register_new_user_password_first" with "mypassword"
    And I fill in "register_new_user_password_second" with "mypassword"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "Mon profil"
    And I should not see "register.step"

  Scenario: User already exists
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then I should be on this page "/fr/login-second-step"
    And I should see "flash.event.register.already_known.message"

  Scenario: Login successful
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "test@test.com"
    When I fill in "login_password" with "p@ssw0rd"
    And I press "login.connect"
    Then the response status code should be 200
    And I should be on this page "/fr/"

  Scenario: Login failed
    And I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/login"
    And I fill in "email_email" with "test@test.com"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "test@test.com"
    When I fill in "login_password" with "wrong-p@ssw0rd"
    And I press "login.connect"
    Then the response status code should be 200
    And I should see "Bad credentials."

  Scenario: Fill a participant profile
    Given I am logged with "test@test.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then I should be on this page "/fr/participate/1"
    When I fill in the following:
      | Prénom             | Paul         |
      | Nom                | Dupont       |
      | Téléphone portable | +33698765432 |
      | Téléphone fixe     | +33198765432 |
    Then I check the "gender.man" radio
    And I select "Informatique" from "block[6c4a3a4f][item][first]"
    And I select "Ingénieur chef de projet" from "block[6c4a3a4f][item][second]"
    And I press "common.next"
    Then the response status code should be 200

  Scenario: Redirect on registration unfinished step
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/"
    And I check the "Fournisseur" radio
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "email_email" with "test_unfinished_step@test.com"
    And I press "common.next"
    Then the response status code should be 200
    When I fill in "register_new_user_password_first" with "p@ssw0rd"
    And I fill in "register_new_user_password_second" with "p@ssw0rd"
    And I press "common.next"
    Then the response status code should be 200
    Then I should see "Profil"
    And I should see "register.step"
    And I should see "1/3"
    When I fill in the following:
      | Prénom             | Paul         |
      | Nom                | Dupont       |
      | Téléphone portable | +33698765432 |
      | Téléphone fixe     | +33198765432 |
    Then I check the "gender.man" radio
    And I select "Informatique" from "block[6c4a3a4f][item][first]"
    And I select "Ingénieur chef de projet" from "block[6c4a3a4f][item][second]"
    And I press "common.next"
    Then the response status code should be 200
    And I should see "register.step"
    And I should see "2/3"
    When I press "common.next"
    Then the response status code should be 200
    When I go to this page "http://asddays-2016.vimeet.proximum/app_test.php/fr/"
    Then I should be on this page "/fr/participant/5/step/3"
    And I should see "register.step"
    And I should see "3/3"
    And I fill in the following:
      | Nom (Société / Organisme)     | Elao              |
      | block[97ed778d][item][first]  | category1         |
      | Adresse                       | 10 rue Saint Marc |
      | Code postal                   | 75002             |
      | Ville                         | Paris             |
      | Pays                          | FR                |
      | block[57da9df7ced30][boolean] | 1                 |
      | Décrivez votre activité       | abc               |
    When I press "register.finalize"
    Then I should be on this page "/fr/sheet/5"
    When I follow "navigation.links.notification"
    Then I should be on this page "/fr/sheet/5/notification"
    And I should see "notification.list.title.label"
    And I should see "notification.package.noOrder"
    And I should see "notification.label.required"
    And I should see "notification.category.package.label"
