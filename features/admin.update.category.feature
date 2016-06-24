@admin

Feature: Update a participation category
  I need to be able to update category title and types

  Scenario: I can list event categories
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/category"
    Then I should see "Exposant"
    And I should see "Visiteur"

  Scenario: I can add a category
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/category/create"
    When I fill in the following:
      | category_create_translations_fr_title | category title fr |
      | category_create_translations_en_title | category title en |
    And I check "category_create_types_0"
    And I press "form.category_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.category.create.success"

  Scenario: I can add an remove a type in a category
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/category/1/update"
    And the "category_update_types_0" checkbox should be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should not be checked
    When I check "category_update_types_2"
    And I uncheck "category_update_types_0"
    And I press "form.category_update.children.submit.label"
    Then I should see "flash.admin.category.update.success"
    When I go to "/admin/fr/event/1/category/1/update"
    Then the "category_update_types_0" checkbox should not be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should be checked
