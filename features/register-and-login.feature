Feature: Register and login user
  I need to be able to register to an event and login to my account

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Event.yml" are loaded

  Scenario: Register an user
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I follow "Exposant"
    And I fill in "Email" with "test@test.com"
    And I fill in "Mot de passe" with "p@ssw0rd"
    And I fill in "Ressaisir le mot de passe" with "p@ssw0rd"
    And I press "Envoyer"
    Then I should see "Votre compte a bien été créé"

  Scenario: User already exists
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I follow "Exposant"
    And I fill in "Email" with "test@test.com"
    And I fill in "Mot de passe" with "p@ssw0rd"
    And I fill in "Ressaisir le mot de passe" with "p@ssw0rd"
    And I press "Envoyer"
    Then I should see "Un compte associé à cette adresse mail existe déjà"

  Scenario: Login successful
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "Email" with "test@test.com"
    And I fill in "Mot de passe" with "p@ssw0rd"
    And I press "Se connecter"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I should see "Bonjour test@test.com"

  Scenario: Login failed
    When the fixtures "User.yml" are loaded
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "Email" with "test@test.com"
    And I fill in "Mot de passe" with "whatever-wrong-password"
    And I press "Se connecter"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I should see "Identifiants invalides"
