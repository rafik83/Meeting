@admin
@admin-event
@admin-unavailability
Feature: See, create and update mass unavailability
  I need to be able to see, create and update a mass unavailability

  Scenario: I can create a mass unavailability
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml                   |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml                |
      | @InfrastructureBundle/DataFixtures/ORM/Unavailability/RdvCarnot2016-Category.yml |
      | Admin.yml                                                                        |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/unavailability/mass"
    And I follow "admin.unavailability.mass.add"
    And I should be on this page "/admin/fr/event/1/unavailability/mass/create"
    When I fill in the following:
      | unavailability_mass_create[category]                | 1                |
      | unavailability_mass_create[name]                    | MyName           |
      | unavailability_mass_create[begin]                   | 10/10/2016 10:10 |
      | unavailability_mass_create[end]                     | 10/10/2016 12:10 |
      | unavailability_mass_create[blocking]                | 0                |
      | unavailability_mass_create[translations][fr][title] | MassEVENT        |
      | unavailability_mass_create[translations][en][title] | MassEVENT        |
    And I press "form.unavailability_mass_create.children.submit.label"
    Then I should see "flash.admin.unavailability.mass.create.success"
    And I should see "MyName"
    And I should see "MassEVENT"

  Scenario: I can update a mass unavailability
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/unavailability/mass/update/1"
    When I fill in the following:
      | unavailability_mass_update[category]                | 1                |
      | unavailability_mass_update[name]                    | SUPERNAME        |
      | unavailability_mass_update[blocking]                | 1                |
      | unavailability_mass_update[begin]                   | 12/10/2016 12:10 |
      | unavailability_mass_update[end]                     | 12/10/2016 14:10 |
      | unavailability_mass_update[translations][fr][title] | SUPERMASS        |
      | unavailability_mass_update[translations][en][title] | SUPERMASS        |
    And I press "form.unavailability_mass_update.children.submit.label"
    Then I should see "flash.admin.unavailability.mass.update.success"
    And I should not see "MassEVENT"
    And I should see "SUPERMASS"
    And I should see "SUPERNAME"
