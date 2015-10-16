Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Event.yml" are loaded

  Scenario: Show the homepage of 'Les rendez-vous Carnot 2016' in French
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I should see "Les rendez-vous CARNOT 2016"
    Then I should see "LES RENDEZ-VOUS"

  Scenario: Show the homepage of 'Les rendez-vous Carnot 2016' in English
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/en/"
    Then I should see "Les rendez-vous Carnot 2016"
    Then I should see "In 7 editions, les Rendez-vous CARNOT became the major R&D event for innovation."
