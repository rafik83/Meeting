Feature: Administer choice type template field
  I need to be able to add and update a choice fiel

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | Admin.yml        |
    Given I am logged with "test@test.com" on admin

  Scenario: I can add a choice field
    Given I am on this page "/admin/fr/event/1/type/1/form/field/add/lib_choice/5/to/participant/default"
    When I fill in the following:
      | admin_lib_choice_label_fr       | label fr       |
      | admin_lib_choice_label_en       | label en       |
      | admin_lib_choice_placeholder_fr | placeholder fr |
      | admin_lib_choice_placeholder_en | placeholder en |
    And I press "admin_lib_choice_submit"
    Then the response status code should be 200
    And I should see "flash.admin.type_template_field.create.success"

  Scenario: I can update an item in a choice items collection
    Given I am on this page "/admin/fr/event/1/type/1/form/field/update/sheet/563cae566af03/5641f59e537b9"
    And the "admin_lib_choice_choices_status3_label_fr" field should contain "SARL"
    And the "admin_lib_choice_choices_status3_label_en" field should contain "SARL"
    When I fill in the following:
      | admin_lib_choice_choices_status3_label_fr | status label fr |
      | admin_lib_choice_choices_status3_label_en | status label en |
    And I press "admin_lib_choice_submit"
    Then the response status code should be 200
    And I should see "flash.admin.type_template_field.update.success"
    And I go to this page "/admin/fr/event/1/type/1/form/field/update/sheet/563cae566af03/5641f59e537b9"
    And the "admin_lib_choice_choices_status3_label_fr" field should contain "status label fr"
    And the "admin_lib_choice_choices_status3_label_en" field should contain "status label en"


