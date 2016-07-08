@admin

Feature: Manage products selection templates
  As an Admin, I need to be able to add, update and list products selection templates

  Scenario: Create a package template
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml   |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml       |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml     |
      | Admin.yml                                                             |
     And I am logged with "test@test.com" on admin
     And I am on this page "/admin/fr/event"
     And I follow "admin.package.link"
     And I should be on this page "/admin/fr/template/package"
    When I fill in "form.package_create.children.title.label" with "My template"
     And I select "ASD Days" from "form.package_create.children.event.label"
     And I press "form.package_create.children.submit.label"
    Then I should be on this page "/admin/fr/template/package/2/update"
     And the "form.package_update.children.title.label" field should contain "My template"

  Scenario: Update a package template
    Given the database is empty
      And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml               |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml   |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml       |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml     |
      | Admin.yml                                                             |
      And I am logged with "test@test.com" on admin
      And I am on this page "/admin/fr/event"
      And I follow "admin.package.link"
      And I should be on this page "/admin/fr/template/package"
     Then I follow "admin.package.update"
      And I should be on this page "/admin/fr/template/package/1/update"
     When I fill in the following:
      | package_update_plans_labels_fr                  | Mes formules  |
      | package_update_plans_labels_en                  | My packages   |
      | package_update_participantAndPlanning_labels_fr | Participants  |
      | package_update_participantAndPlanning_labels_en | Participants  |
      | package_update_options_labels_fr                | Options       |
      | package_update_options_labels_en                | Other Options |
     And I check "package_update_plans_enabled"
     And I check "package_update_participantAndPlanning_enabled"
     And I check "package_update_options_enabled"
     And I press "form.package_update.children.submit.label"
    Then I should be on this page "/admin/fr/template/package/1/update"
     And I should see "flash.admin.template.package.update.success"
      And the "package_update_plans_labels_fr" field should contain "Mes formules"
      And the "package_update_plans_labels_en" field should contain "My packages"
      And the "package_update_participantAndPlanning_labels_fr" field should contain "Participants"
      And the "package_update_participantAndPlanning_labels_en" field should contain "Participants"
      And the "package_update_options_labels_fr" field should contain "Options"
      And the "package_update_options_labels_en" field should contain "Other Options"
