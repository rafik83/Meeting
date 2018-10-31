@event @agenda @program @happening @mass @unavailability
Feature: Agenda
  As a participant, I can see my agenda

  Scenario: I can see my agenda
    Given the database is purged
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
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I go to this page "/fr/sheet/2/agenda"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should see "Jeudi 13 octobre 2016"
    And I should see "Cocktail"
    And I should not see "Repas"
    Then I go to this page "/fr/sheet/2"
    And I should see "sheet.object.action.add"
    Then I follow "sheet.object.action.add"
    And I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Pascal                       |
      | add_participant_lastName  | MICHELIN                     |
      | add_participant_email     | pascal.michelin2@example.net |
    And I should see "Participant supplémentaire"
    Then I press "sheet.participant.sendInvite"
    And I should be on this page "/fr/sheet/2/fr"
    When I go to this page "/fr/sheet/2/agenda"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "agenda.participant.view"
    Then I go to this page "/fr/sheet/2/agenda/participant/4"
    And the response status code should be 200

  Scenario: I can see my agenda when I have multiple sheets
    Given the database is purged
    And the event "Best of web" is created
    And there is 2 slot in this event
    And there is an active spot "SPOTA1" with meeting capacity of 2, seat capacity of 6
    And there is a sheet with the title "SheetA"
    And the user "user@example.net" is created
    And there is a participant for this sheet and this user
    And there is a sheet with the title "SheetB"
    And there is a participant for this sheet and this user
    And there is a sheet with the title "SheetC"
    And there is a participant for this sheet
    And there is a meeting between "SheetA" and "SheetC" on spot "SPOTA1"
    And there is a meeting between "SheetB" and "SheetC" on spot "SPOTA1"
    And this event occurs today from "08:00" to "18:00"
    And the agenda is open
    And the meetings are published
    When I am on the homepage of this event
    And I am logged with this user
    And I go to this page "/fr/sheet/1/agenda/participant/1"
    Then I should see "SPOTA1 - SheetA - SheetC"
    And I should see "SPOTA1 - SheetB - SheetC"
