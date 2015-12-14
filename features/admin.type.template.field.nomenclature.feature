Feature: Update type template field nomenclature
  I need to be able to update collection of nomenclature items

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |

  Scenario: I can update an item in a nomenclature collection
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/type/1/form"
    And I should see "SARL"
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event/1/type/1/form/field/update/sheetTemplate/5641f59e537b9"
    Then I fill in the following:
      | type_template_field_update_lib_choice_choices_status3_label_fr | status label fr |
      | type_template_field_update_lib_choice_choices_status3_label_en | status label en |
    And I press "type_template_field_update_lib_choice_submit"
    Then the response status code should be 200
    And I should see "flash.admin.type_template_field.update.success"
    And I should see "status label fr"
    And I should not see "SARL"
