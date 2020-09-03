@event @agenda @program @happening @mass @unavailability
Feature: Agenda
  As a participant, I can see my agenda

  Scenario: I can see my agenda
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And this event occurs the "2016-10-12" from "08:00" to "18:00"
    And this event occurs the "2016-10-13" from "08:00" to "18:00"
    And the agenda is open
    And there is a type "Fournisseur" in this event
    And there is a package "Pack participants+rdv" for this event
    And there is a product Participant called "Participant Supplémentaire" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And this package is assigned to this type
    And there is a mass unavailability "Cocktail" for this type the 2016-10-12 from 10:00 to 11:00
    And the user "user_asddays_2@proximum.com" is created
    And this user is declared in this event
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And there is a type "Donneur d'ordre" in this event
    And there is a mass unavailability "Repas" for this type the 2016-10-12 from 10:00 to 11:00
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/agenda"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should see "Jeudi 13 octobre 2016"
    And I should see "Cocktail"
    And I should not see "Repas"
    Then I go to this page "/fr/sheet/1"
    And I should see "sheet.object.action.add"
    Then I follow "sheet.object.action.add"
    And I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Pascal                       |
      | add_participant_lastName  | MICHELIN                     |
      | add_participant_email     | pascal.michelin2@example.net |
    And I should see "Participant supplémentaire"
    Then I press "sheet.participant.sendInvite"
    And I should be on this page "/fr/sheet/1/fr"
    When I go to this page "/fr/sheet/1/agenda"
    Then I should be on this page "/fr/sheet/1/agenda/sheet"
    And I should see "agenda.myAgenda.button.title"
    And I should see "agenda.sheetAgenda.button.title"
    Then I go to this page "/fr/sheet/1/agenda/participant/2"
    And the response status code should be 200

  Scenario: I can see my agenda when I have multiple sheets
    Given the database is purged
    And the event "Best of web" is created
    And there are 2 slots in this event
    And there is an active spot "SPOTA1" with size of 1, meeting capacity of 2, seat capacity of 6
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
