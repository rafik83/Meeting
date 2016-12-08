@admin
@admin-event
@admin-unavailability
Feature: See, create and update unavailability category
  I need to be able to see, create and update an unavailability category

  Scenario: I can create an unavailability category
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                     |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml       |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml                |
      | Admin.yml                                                                   |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/unavailability/category"
    And I follow "admin.unavailability.category.add"
    And I should be on this page "/admin/fr/event/1/unavailability/category/create"
    When I fill in the following:
      | unavailability_category_create[picto]      | Dejeuner   |
      | unavailability_category_create[title]      | MyCategory |
      | unavailability_category_create[leftColor]  | #59a4eb    |
      | unavailability_category_create[rightColor] | #00398c    |
    And I press "form.unavailability_category_create.children.submit.label"
    Then I should see "flash.admin.unavailability.category.create.success"
    And I should see "MyCategory"

  Scenario: I can update an unavailability category
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/unavailability/category/update/1"
    When I fill in the following:
      | unavailability_category_update[picto]      | Dejeuner       |
      | unavailability_category_update[title]      | MyNewCategory2 |
      | unavailability_category_update[leftColor]  | #123123        |
      | unavailability_category_update[rightColor] | #456456        |
    And I press "form.unavailability_category_update.children.submit.label"
    Then I should see "flash.admin.unavailability.category.update.success"
    And I should not see "MyCategory"
    And I should see "MyNewCategory2"
