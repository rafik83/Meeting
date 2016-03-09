Feature: Add and delete participant
  I need to be able to add and delete participant on a sheet of an event

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                |
      | app/Event.yml                   |
      | app/Type.yml                    |
      | User.yml                        |
      | Sheet.yml                       |
      | OneSheetSeveralParticipants.yml |

  Scenario: I can delete a participant as an owner
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "/fr/sheet/1"
    And the response status code should be 200
    And I should see "Exposant"
    And I should see "form.delete_participant.children.submit.label"
    Then I press "form.delete_participant.children.submit.label"
    And the response status code should be 200
    And I should see "flash.sheet.delete_participant.success"

  Scenario: I can not delete a participant as a guest
    Given I am logged with "test-2@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "/fr/sheet/1"
    And the response status code should be 200
    And I should see "Exposant"
    And I should not see "form.delete_participant.children.submit.label"
