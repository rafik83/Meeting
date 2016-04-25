Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Background:
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml     |
      | app/Event.yml        |
      | Nomenclatures.yml    |
      | app/Type.yml         |

  Scenario: Show the participant types of 'Les rendez-vous Carnot 2016' in French
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I should see "Exposant"
    Then I should see "Visiteur"

  Scenario: Show the participant types of 'Les rendez-vous Carnot 2016' in English
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/en/"
    And the response status code should be 200
    Then I should see "Exhibitor"
    Then I should see "Visitor"
