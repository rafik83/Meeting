Feature: add type
  I need to be able to add a type

  Scenario: add a type
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/admin/fr/event/1/type"
    And I follow "admin.type.create.link"
    Then the response status code should be 200
    And I fill in the following:
      | type_create_sheetTemplate         | 0    |
      | type_create_registrationTemplate  | 0    |
      | type_create_package               | 0    |
      | type_create_translations_fr_title | Test |
      | type_create_translations_en_title | Test |
      | type_create_position              | 1    |
    And I press "form.type_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.create.success"
