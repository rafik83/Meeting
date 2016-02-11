Feature: Update category
  I need to be able to update category title and types

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | app/Category.yml |

  Scenario: I can list event categories
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category"
    Then the response status code should be 200
    And I should see "Exposant"
    And I should see "Visiteur"

  Scenario: I can add a category
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category/create"
    Then the response status code should be 200
    And I fill in the following:
      | category_create_translations_fr_title | category title fr |
      | category_create_translations_en_title | category title en |
    And I check "category_create_types_0"
    And I press "form.category_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.category.create.success"

  Scenario: I can add a type in a category
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category/1/update"
    Then the response status code should be 200
    And the "category_update_types_0" checkbox should be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should not be checked
    And I check "category_update_types_2"
    And I press "form.category_update.children.submit.label"
    And I should see "flash.admin.category.update.success"
    Then I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category/1/update"
    And the "category_update_types_0" checkbox should be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should be checked

  Scenario: I can remove a type in a category
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category/1/update"
    Then the response status code should be 200
    And the "category_update_types_0" checkbox should be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should not be checked
    And I uncheck "category_update_types_0"
    And I press "form.category_update.children.submit.label"
    And I should see "flash.admin.category.update.success"
    Then I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/category/1/update"
    And the "category_update_types_0" checkbox should not be checked
    And the "category_update_types_1" checkbox should be checked
    And the "category_update_types_2" checkbox should not be checked
