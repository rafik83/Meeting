@admin
@admin-event
@admin-happening
Feature: See, create and update happening category
  I need to be able to see, create and update an happening category

  Scenario: I can see the list of happening categories
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Happening/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/happening/category"
    Then I should see "Présentation flash"
    And I should see "Conférence"
    And I should see "Atelier"
    And I should see "Cocktail"
    And I should see "Réunion"
    And I should see "Table ronde"
    And I should see "picto2"
    And I should see "6"

  Scenario: I can create a happening category
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event/1/happening/category"
    And I follow "admin.happening_category.add"
    And I should be on this page "/admin/en/event/1/happening/category/create"
    When I fill in the following:
      | category_create[position]                | 1             |
      | category_create[picto]                   | picto1        |
      | category_create[translations][fr][title] | MyCategory    |
      | category_create[translations][en][title] | MyCategory    |
      | category_create[leftColor]               | #59a4eb       |
      | category_create[rightColor]              | #00398c       |
    And I press "form.category_create.children.submit.label"
    Then I should see "flash.admin.happening.category.create.success"
    And I should see "MyCategory"

  Scenario: I can update a happening category
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/happening/category/7/update"
    When I fill in the following:
      | category_update[position]                | 2                |
      | category_update[picto]                   | picto1           |
      | category_update[translations][fr][title] | MyNewCategory    |
      | category_update[translations][en][title] | MyNewCategory    |
      | category_update[leftColor]               | #59a4eb          |
      | category_update[rightColor]              | #00398c          |
    And I press "form.category_update.children.submit.label"
    Then I should see "flash.admin.happening.category.update.success"
    And I should not see "MyCategory"
    And I should see "MyNewCategory"
