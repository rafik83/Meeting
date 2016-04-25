Feature: Test to go to the participant sheet
  I need to be able to register to an event and login to my account

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

  Scenario: I can go to the participant sheet of the user
    When I follow "event.link.see_my_sheet"
    Then I should be on "/fr/fiche-de-presentation"
    And the response status code should be 200
