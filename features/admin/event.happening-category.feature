@admin
@admin-event
@admin-happening
Feature: See, create and update happening category
  I need to be able to see, create and update an happening category

  Scenario: I can see the list of happening categories
    Given the database is purged
    And the event "La palourde en fête" is created
    And there is an happening category called "Cocktail" for this event
    And I am logged as admin
    When I go to this page "/fr/event/1/happening/category"
    Then I should see "Cocktail"

  Scenario: I can create a happening category
    Given I am logged as admin
    And I am on this page "/fr/event/1/happening/category"
    And I follow "admin.happening_category.add"
    And I should be on this page "/fr/event/1/happening/category/create"
    When I fill in the following:
      | category_create[rank]                    | 1             |
      | category_create[picto]                   | Dejeuner      |
      | category_create[translations][fr][title] | MyCategory    |
      | category_create[translations][en][title] | MyCategory    |
      | category_create[leftColor]               | #59a4eb       |
      | category_create[rightColor]              | #00398c       |
    And I press "form.category_create.children.submit.label"
    Then I should see "flash.admin.happening.category.create.success"
    And I should see "MyCategory"

  Scenario: I can update a happening category
    Given I am logged as admin
    And I am on this page "/fr/event/1/happening/category/2/update"
    When I fill in the following:
      | category_update[rank]                    | 2              |
      | category_update[picto]                   | Dejeuner       |
      | category_update[translations][fr][title] | MyNewCategory  |
      | category_update[translations][en][title] | MyNewCategory  |
      | category_update[leftColor]               | #59a4eb        |
      | category_update[rightColor]              | #00398c        |
    And I press "form.category_update.children.submit.label"
    Then I should see "flash.admin.happening.category.update.success"
    And I should not see "MyCategory"
    And I should see "MyNewCategory"
