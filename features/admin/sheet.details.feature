@admin @sheet @mail
  
Feature: See sheet details
  As an admin, I can see the details of a sheet

  Scenario: I can see the details of a sheet
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Order.yml           |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I go to "/fr/event"
    When I go to this page "/fr/event/past"
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on this page "/fr/event/1/sheet"
    And I should see "WorldCompanyInc"
    Then I go to this page "/fr/event/1/sheet/1"
    And I should see "WorldCompanyInc"
    And I should see "Paul"
    And I should see "Gascoigne"
    And I should see "admin.sheet.details.meeting.request.approved"
    And I should see "admin.sheet.details.meeting.request.pending"
    And I should see "admin.sheet.details.meeting.request.refused"
    And I should see "admin.sheet.details.meeting.proposition.refused"
    And I should see "admin.sheet.details.company.title"
    And I should see "Chiffre d'affaires"
    And I should see "Nom (Société / Organisme)"
    And I should see "Ville"
    And I should see "Adresse"
    And I should see "Site internet"
    And the "is-visio" checkbox should be checked

  Scenario: I can add a comment on a sheet
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/fr/event/1/sheet/1"
    And I should see "WorldCompanyInc"
    Then I fill in the following:
      | sheet_comment_text | This is a test |
    And I press "form.sheet_comment.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.add_comment.success"
    And I should see "This is a test"
    And I should see "admin.sheet.details.comments.author"

  Scenario: I can change the sheet type
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/fr/event/1/sheet/1"
    And I should see "WorldCompanyInc"
    And the ".label-sheet-type" element should contain "Exposant"
    And I should not see "admin.sheet.trace.changed_type"
    Then I check the "Investisseur" radio
    And I press "form.change_type.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.change_type.success"
    And the "sheet.changed_type" mail should be sent to "test@elao.com" from "no-reply@rdv-carnot-2016.vimeet.proximum"
    And the "sheet.changed_type" mail should be sent in bcc to "team-project@example.net" from "no-reply@rdv-carnot-2016.vimeet.proximum"
    And the ".label-sheet-type" element should contain "Investisseur"
    And I should see "admin.sheet.trace.changed_type"

  Scenario: I cant change a sheet type and I can see generated invoice, and cannot edit order when sheet has at least one invoiced order
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/fr/event/1/sheet/21"
    And I should not see "form.change_type.children.submit.label"
    And I should see "Rdv2017-0001"
    And I should not see "admin.order_edit.link"
