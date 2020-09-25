@admin

Feature: Update a participation category
  I need to be able to update category title and types

  Scenario: I can list event categories
    Given the database is purged
    And the event "RDV Carnot" is created
    And there is a type "PME" in this event
    And there is a type "Grand groupe" in this event
    And there is a participant category "Exposant" for this event
    And this category contains all types
    And there is a participant category "Visiteur" for this event
    And this category contains all types
    And there is a type "Administration" in this event
    And I am logged as admin
    When I go to this page "/fr/event/1/category"
    Then I should see "Exposant"
    And I should see "Visiteur"

  Scenario: I can add a category
    Given I am logged as admin
    And I am on this page "/fr/event/1/category/create"
    When I fill in the following:
      | category_create_translations_fr_title | category title fr |
      | category_create_translations_en_title | category title en |
    And I check "PME"
    And I press "form.category_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.category.create.success"

  Scenario: I can remove a type in a category
    Given I am logged as admin
    And I am on this page "/fr/event/1/category/1/update"
    Then the "PME" checkbox should be checked
    And the "Grand groupe" checkbox should be checked
    And the "Administration" checkbox should not be checked
    When I check "Administration"
    And I uncheck "PME"
    And I press "form.category_update.children.submit.label"
    Then I should see "flash.admin.category.update.success"
    When I go to "/fr/event/1/category/1/update"
    Then the "PME" checkbox should not be checked
    And the "Grand groupe" checkbox should be checked
    And the "Administration" checkbox should be checked

