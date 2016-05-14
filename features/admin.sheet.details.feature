Feature: See sheet details
  As an admin, I can see the details of a sheet

  Scenario: I can see the details of a sheet
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on this page "/admin/fr/event/1/sheet"
    And I should see "WorldCompanyInc"
    Then I go to this page "/admin/fr/event/1/sheet/1"
    And I should see "WorldCompanyInc"
    And I should see "Paul"
    And I should see "Gascoigne"
    And I should see "Demandes validée"
    And I should see "Propositions en attente"
    And I should see "Demandes refusées"
    And I should see "Propositions refusées"

  Scenario: I can add a comment on a sheet
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/admin/fr/event/1/sheet/1"
    And I should see "WorldCompanyInc"
    Then I fill in the following:
      | sheet_comment_text | This is a test |
    And I press "form.sheet_comment.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.add_comment.success"
    And I should see "This is a test"
    And I should see "admin.sheet.details.comments.author"
