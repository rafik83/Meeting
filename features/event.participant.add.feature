Feature: Add participant
  I need to be able to add a participant

  Scenario: I can a participant to my sheet
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016Event.yml              |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    And I follow "event.link.see_my_sheet"
    Then I should be on this page "/fr/sheet"
    And I should see "sheet.object.action.add"
    Then I follow "sheet.object.action.add"
    And I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Truc         |
      | add_participant_lastName  | Test         |
      | add_participant_email     | truc@test.fr |
    Then I press "sheet.participant.sendInvite"
    And I should be on this page "/fr/sheet/fr"
    And I should see "Truc TEST"
    And I should see "TT"
    And the "user_activate_account" mail should be sent to "truc@test.fr"
