@event
@type
Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Scenario: I can see the event name
    Given the database is purged
    And the event "Les rendez-vous CARNOT" is created
    And the domain for this event is "rdv-carnot.vimeet.proximum"
    And there is a type "Exposant" in this event
    And the "en" translation of this type is "Exhibitor"
    And there is a type "Visiteur" in this event
    And the "en" translation of this type is "Visitor"
    When I go to this page "http://rdv-carnot.vimeet.proximum/fr/"
    Then I should see "Les rendez-vous CARNOT"

  Scenario: Show the participant types of 'Les rendez-vous Carnot' in French
    Given there is an event with domain "rdv-carnot.vimeet.proximum"
    When I go to this page "http://rdv-carnot.vimeet.proximum/fr/"
    Then I should see "Exposant"
    And I should see "Visiteur"

  Scenario: Show the participant types of 'Les rendez-vous Carnot' in English
    Given there is an event with domain "rdv-carnot.vimeet.proximum"
    When I go to this page "http://rdv-carnot.vimeet.proximum/en/"
    Then I should see "Exhibitor"
    And I should see "Visitor"

  Scenario: See pratical info of the event
    Given there is an event with domain "rdv-carnot.vimeet.proximum"
    And the organiser name of this event is "PROXIMUM"
    And the organiser email of this event is "accounts@proximumgroup.com"
    When I go to this page "http://rdv-carnot.vimeet.proximum/fr/"
    Then I should see "event.info.contact"
    And I should see "PROXIMUM"
    And I should see "accounts@proximumgroup.com"
    And I should see "event.info.close"
