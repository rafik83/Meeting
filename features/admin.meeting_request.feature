Feature: See meeting request
  I can see meeting requests

  Scenario: list meeting request
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/EventRdvCarnot2016.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/TypeRdvCarnot2016.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/Sheet.yml                         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/Participant.yml                   |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/Request.yml               |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I follow "admin.meeting_request.link"
    And I should be on this page "/admin/fr/event/1/meeting-request"
    And I should see "WorldCompanyInc"

