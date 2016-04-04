Feature: See sheet details
  As an admin, I can see the detaisl of a sheet

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | User.yml                               |
      | TwoSheetSeveralParticipantWithData.yml |
      | Admin.yml                              |
    Given elastica is populate
    And I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"

  Scenario: I can see the details of a sheet
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/sheet"
    And I should see "Elao"
    Then I go to this page "/admin/fr/event/1/sheet/1"
    And I should see "Elao"
    And I should see "Jean Dutest"
    And I should see "Test Super"
    And I should see "Demandes validée"
    And I should see "Propositions en attente"
    And I should see "Demandes refusées"
    And I should see "Propositions refusées"

  Scenario: I can add a comment on a sheet
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/sheet"
    And I should see "Elao"
    Then I go to this page "/admin/fr/event/1/sheet/1"
    And I should see "Elao"
    Then I fill in the following:
    | sheet_comment_text | This is a test |
    And I press "form.sheet_comment.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.add_comment.success"
    And I should see "This is a test"
    And I should see "admin.sheet.details.comments.author"
