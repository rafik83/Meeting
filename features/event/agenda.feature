@event @agenda @program @happening @mass @unavailability
Feature: Agenda
  As a participant, I can see my agenda

  Scenario: I can see my agenda
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                    |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml   |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/Unavailability/ASDDays2016-Mass.yml |
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/agenda"
    Then I should be on this page "/fr/agenda/participant/2"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should see "Jeudi 13 octobre 2016"
    And I should see "cocktail"
    Then I go to this page "/fr/sheet"
    And I should see "sheet.object.action.add"
    Then I follow "sheet.object.action.add"
    And I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Pascal                       |
      | add_participant_lastName  | MICHELIN                     |
      | add_participant_email     | pascal.michelin2@example.net |
    And I should see "Participant supplémentaire"
    Then I press "sheet.participant.sendInvite"
    And I should be on this page "/fr/sheet/fr"
    When I go to this page "/fr/agenda"
    Then I should be on this page "/fr/agenda/participant/2"
    And I should see "agenda.participant.view"
    Then I go to this page "/fr/agenda/participant/4"
    And the response status code should be 200

