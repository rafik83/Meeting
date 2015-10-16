Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Background:
    Given the database is initialized
    And the fixtures "Event.yml" are loaded

  Scenario: Show the homepage of 'Les rendez-vous Carnot 2016'
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php"
    Then I should see "Les rendez-vous CARNOT 2016"
    Then I should see "LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISES"
