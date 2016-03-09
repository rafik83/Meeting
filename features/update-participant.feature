Feature: Update self participant sheet
  I need to be able to update my participant informations

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                |
      | app/Event.yml                   |
      | app/Type.yml                    |
      | User.yml                        |
      | Sheet.yml                       |
      | OneSheetSeveralParticipants.yml |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"

  Scenario: I can go to the participant sheet and update it
    When I follow "event.link.see_my_sheet"
    Then the response status code should be 200
    And I follow "event.sheet.block.update_participant.title"
    And I fill in the following:
      | Nom       | Jean        |
      | Prénom    | Dupond      |
      | Téléphone | 0611111111  |
      | Fonction  | position4   |
    And I press "form.participant_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.sheet.update_participant.success"
