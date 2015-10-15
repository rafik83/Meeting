Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Background:
    Given the database is initialized
    And the fixtures "Event.yml" are loaded

  Scenario: Show the homepage of 'Les rendez-vous Carnot 2016'
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    Then I should see "Les rendez-vous Carnot 2016"
    Then I should see "Participez aux Rendez-vous CARNOT 2016 : le rendez-vous professionnel de la recherche au service des entreprises et des collectivités, les 18 et 19 novembre 2016 / Paris / France."
