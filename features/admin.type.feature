Feature: add type
  I need to be able to add a type

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml    |
      | app/Event.yml       |
      | app/Type.yml        |
      | app/Category.yml    |

  Scenario: add a type
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    And I follow "Types"
    Then the response status code should be 200
    And I should be on "http://vimeet.proximum.dev/app_test.php/admin/event/1/type"
    And I follow "Ajouter"
    Then the response status code should be 200
    And I fill in the following:
      | type_create_template              | 1    |
      | type_create_translations_fr_title | Test |
      | type_create_translations_en_title | Test |
    And I press "form.type_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.create.success"
