Feature: Manage products selection templates
  As an Admin, I need to be able to add, update and list products selection templates

  Scenario: Create a products selection template
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016Event.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml |
      | Admin.yml                                                      |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    And I follow "admin.template.productsSelection.link"
    And I should be on this page "/admin/fr/template/products-selection"
    When I fill in "form.template_products_selection_create.children.title.label" with "My template"
    And I select "ASD Days" from "form.template_products_selection_create.children.event.label"
    And I press "form.template_products_selection_create.children.submit.label"
    Then I should be on this page "/admin/fr/template/products-selection/1/update"
    When I fill in the following:
      | template_products_selection_update_templateData_blocks_0_label_fr | Mes formules  |
      | template_products_selection_update_templateData_blocks_0_label_en | My packages   |
      | template_products_selection_update_templateData_blocks_1_label_fr | Participants  |
      | template_products_selection_update_templateData_blocks_1_label_en | Participants  |
      | template_products_selection_update_templateData_blocks_2_label_fr | Options       |
      | template_products_selection_update_templateData_blocks_2_label_en | Other Options |
    And I check "template_products_selection_update_templateData_blocks_0_enabled"
    And I check "template_products_selection_update_templateData_blocks_1_enabled"
    And I check "template_products_selection_update_templateData_blocks_2_enabled"
    And I press "form.template_products_selection_update.children.submit.label"
    Then I should be on this page "/admin/fr/template/products-selection"
    And I should see "flash.admin.template.products_selection.update.success"
    And I should see "My template"
    And I should see "ASD Days"

