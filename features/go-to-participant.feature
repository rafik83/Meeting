Feature: Register and login user
  I need to be able to register to an event and login to my account

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Participant.yml" are loaded

  Scenario: I can go to the participant sheet of the user
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "Email" with "test@test.com"
    And I fill in "Mot de passe" with "p@ssw0rd"
    And I press "Se connecter"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I follow "Voir ma fiche de participation"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/participation/1/summary"
    And I should see "Exposant"
    And I should see "Dutest"
