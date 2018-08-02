@admin

Feature: add type
  I need to be able to add a type

  Scenario: add a type
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | Admin.yml                                                                |
      | TypeWithoutSheet.yml                                                     |
    And I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to this page "/fr/event/past"
    And I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I follow "admin.type.create.link"
    Then the response status code should be 200
    And I fill in the following:
      | type_create_sheetTemplate         | 0    |
      | type_create_registrationTemplate  | 0    |
      | type_create_package               | 0    |
      | type_create_translations_fr_title | Test |
      | type_create_translations_en_title | Test |
      | type_create_rank                  | 1    |
    And I check "type_create_hidden"
    And I press "form.type_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.create.success"

  Scenario: edit a type that already have sheet
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/past"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I follow "admin.type.update.link"
    Then I should be on "/fr/event/1/type/1/update"
    And I should see "admin.type.update.title"
    And I should not see "form.type_update.sheetTemplate.label"
    And I should not see "form.type_update.registrationTemplate.label"
    And I should not see "form.type_update.package.label"
    And I fill in the following:
      | type_update_translations_fr_title | TestEdited |
      | type_update_translations_en_title | TestEdited |
      | type_update_rank                  | 2          |
    When I press "form.type_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.update.success"

  Scenario: edit a type that haven't sheet right now
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/past"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I go to this page "/fr/event/1/type/7/update"
    Then I should see "admin.type.update.title"
    And I should see "form.type_update.children.sheetTemplate.label"
    And I should see "form.type_update.children.registrationTemplate.label"
    And I should see "form.type_update.children.package.label"
    And I fill in the following:
      | type_update_translations_fr_title | TestEditedTwo |
      | type_update_translations_en_title | TestEditedTwo |
      | type_update_rank                  | 2             |
    And the "type_update_hidden" checkbox should be checked
    When I press "form.type_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.update.success"

  Scenario: remove a type that haven't sheet right now
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/past"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    And I follow "admin.type.create.link"
    And I fill in the following:
      | type_create_sheetTemplate         | 0          |
      | type_create_registrationTemplate  | 0          |
      | type_create_package               | 0          |
      | type_create_translations_fr_title | TestDelete |
      | type_create_translations_en_title | TestDelete |
      | type_create_rank                  | -1         |
    And I press "form.type_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.create.success"
    When I press "admin.type.remove.link"
    Then I should be on "/fr/event/1/type"
    And I should see "flash.admin.type.remove.success"

  Scenario: I try to remove a type that have a sheet
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/past"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I press "admin.type.remove.link"
    Then I should be on "/fr/event/1/type"
    And I should see "flash.admin.type.remove.error"

  Scenario: I should not see an hidden type
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/app_test.php/en/"
    Then I should not see "Investisseur"

  Scenario: I see type list with associated template
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/past"
    When I go to this page "/fr/event/1/type"
    Then I should see "admin.type.list.title"
    And I should see "Investisseur"
    And I should see "Inscription Template Carnot"
    And I should see "Template RDV Carnot"
    And I should see "Package RDV Carnot"
