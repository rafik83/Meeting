@event @participant @mail
Feature: Manage participant
  I need to be able to add and remove a participant

  Scenario: I can add participant to my sheet
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"

    And there is a type "Fournisseur" in this event
    And there is a package "Pack participants+rdv" for this event
    And there is a product Participant called "Participant Supplémentaire" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And this package is assigned to this type

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "sheet.object.action.add"
    Then I follow "sheet.object.action.add"
    And I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Pascal                      |
      | add_participant_lastName  | MICHELIN                    |
      | add_participant_email     | pascal.michelin@example.net |
    And I should see "Participant supplémentaire"
    Then I press "sheet.participant.sendInvite"
    And the "sheet.participant.add.confirmation" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays-2016.vimeet.proximum"
    And the "user.account_activated" mail should be sent to "pascal.michelin@example.net" from "no-reply@asddays-2016.vimeet.proximum"
    And I should be on this page "/fr/sheet/1/fr"
    And I should see "Pascal MICHELIN"
    # initials of Pascal MICHELIN
    And I should see "PM"

  Scenario: I can remove a participant of my sheet
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I should see "sheet.object.action.remove"
    Then I follow "sheet.object.action.remove"
    ## There is a problem with the radio here as they don't have a label
    ## Therefore the select is used (as it can check radio, don't ask why)
    And I select "0" from "remove_participant[participants][]"
    And I press "sheet.participant.remove"
    And I should be on this page "/fr/sheet/1/fr"
    And I should not see "John DOE"
    And I should not see "JD"
