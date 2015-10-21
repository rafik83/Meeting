Feature: Change password
  When I am logged, I need to be able to change my password

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Event.yml" are loaded
    And the fixtures "User.yml" are loaded

  Scenario: Change the password successfully
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in the following:
      |Email        |test@test.com |
      |Mot de passe |p@ssw0rd      |
    And I press "Se connecter"
    And I should see "Bonjour test@test.com"
    Then I follow "Changer le mot de passe"
    And I fill in the following:
      |Mot de passe actuel               |p@ssw0rd     |
      |Nouveau mot de passe              |new-p@ssw0rd |
      |Ressaisir le nouveau mot de passe |new-p@ssw0rd |
    And I press "Envoyer"
    And I should see "Votre mot de passe a été changé"

  Scenario: Change the password failed
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in the following:
      |Email        |test@test.com |
      |Mot de passe |p@ssw0rd      |
    And I press "Se connecter"
    And I should see "Bonjour test@test.com"
    Then I follow "Changer le mot de passe"
    And I fill in the following:
      |Mot de passe actuel               |whatever-wrong-password     |
      |Nouveau mot de passe              |new-p@ssw0rd                |
      |Ressaisir le nouveau mot de passe |new-p@ssw0rd                |
    And I press "Envoyer"
    And I should see "Le mot de passe actuel n'est pas correct"
