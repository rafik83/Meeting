Feature: Administer text type template field
  I need to be able to add and update a text field

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |

  Scenario: I can add a text field
    Given I am on Admin
    And I go to this page "/admin/event/1/type/1/form/field/add/lib_text/5/to/participant/default"
    When I fill in the following:
      | admin_lib_text_label_fr       | label fr       |
      | admin_lib_text_label_en       | label en       |
    And I press "admin_lib_text_submit"
    Then the response status code should be 200
    And I should see "flash.admin.type_template_field.create.success"
    
  Scenario: I can update a text field
    Given I am on Admin
    And I go to this page "/admin/event/1/type/1/form/field/update/participant/default/563caf1d9b1cb"
    And the "admin_lib_text_label_fr" field should contain "Nom"
    And the "admin_lib_text_label_en" field should contain "Lastname"
    When I fill in the following:
      | admin_lib_text_label_fr | label fr |
      | admin_lib_text_label_en | label en |
    And I press "admin_lib_text_submit"
    Then the response status code should be 200
    And I should see "flash.admin.type_template_field.update.success"
    And I go to this page "/admin/event/1/type/1/form/field/update/participant/default/563caf1d9b1cb"
    And the "admin_lib_text_label_fr" field should contain "label fr"
    And the "admin_lib_text_label_en" field should contain "label en"


