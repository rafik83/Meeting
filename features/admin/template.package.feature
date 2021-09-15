@admin

Feature: Manage products selection templates
  As an Admin, I need to be able to add, update and list products selection templates

  Background:
    Given the database is purged
    And the event "ASD Days" is created
    And I am logged as admin
    And I am on this page "/fr/event"

  Scenario: Create a package template
    When I follow "admin.package.link"
    And I should be on this page "/fr/template/package"
    And I fill in "form.package_create.children.title.label" with "My template"
    And I select "ASD Days" from "form.package_create.children.event.label"
    And I press "form.package_create.children.submit.label"
    Then I should be on this page "/fr/template/package/1/update"
    And the "form.package_update.children.title.label" field should contain "My template"

  Scenario: Update a package template
    Given the event "ASD Days" is created
    And there is a package "Pack stand + 10 RDV" for this event
    And there is a plan called "Formule Little" with a price of "300"
    And there is a product Participant called "Pass Jour 1" with a price of "123" and a max quantity of 1
    And this product participant is assigned to this package
    And this plan includes this product participant 1 time
    And this plan is assigned to this package
    And there is a product Planning called "Planning de RDV PO" with a price of "420" and a max quantity of 10
    When I follow "admin.package.link"
    Then I should be on this page "/fr/template/package"
    When I follow "admin.package.update"
    Then I should be on this page "/fr/template/package/1/update"
    When I fill in the following:
      | package_update_plans_labels_fr                            | Mes formules       |
      | package_update_plans_labels_en                            | My packages        |
      | package_update_participantAndPlanning_labels_fr           | Participants       |
      | package_update_participantAndPlanning_labels_en           | Participants       |
      | package_update_options_labels_fr                          | Options            |
      | package_update_options_labels_en                          | Other Options      |
      | package_update_participantAndPlanning_planning            | 0 |
    And I check "package_update_plans_enabled"
    And I check "package_update_participantAndPlanning_enabled"
    And I check "package_update_options_enabled"
    And I press "form.package_update.children.submit.label"
    Then I should be on this page "/fr/template/package/1/update"
    And I should see "flash.admin.template.package.update.success"
    And the "package_update_plans_labels_fr" field should contain "Mes formules"
    And the "package_update_plans_labels_en" field should contain "My packages"
    And the "package_update_participantAndPlanning_labels_fr" field should contain "Participants"
    And the "package_update_participantAndPlanning_labels_en" field should contain "Participants"
    And the "package_update_options_labels_fr" field should contain "Options"
    And the "package_update_options_labels_en" field should contain "Other Options"
