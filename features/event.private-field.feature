Feature: Test to hide private field
  I need to be able to see that private fields are hidden

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

  Scenario: I can not see private field
    When I follow "event.link.see_my_sheet"
    Then I should be on "/fr/sheet/1"
    And the response status code should be 200
    And I should see "Exposant"
    And I should see "Dutest"
    And I should not see "Téléphone"
    And I should not see "0909090909"
    Then I follow "event.sheet.block.update_participant.title"
    And I should be on "/fr/sheet/1/update_participant/1"
    And the response status code should be 200
    And I should see "Téléphone"
    And I should see "form.field.private"

