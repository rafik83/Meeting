@admin
@admin-event
@admin-unavailability
Feature: See, create and update mass unavailability
  I need to be able to see, create and update a mass unavailability

  Scenario: I can create a mass unavailability
    Given the database is purged
    And the event "To be or not to be" is created
    And there is a mass unavailability category called "Pause" for this event
    And there is a type in this event
    And I am logged as admin
    When I go to this page "/fr/event/1/unavailability/mass"
    And I follow "admin.unavailability.mass.add"
    And I should be on this page "/fr/event/1/unavailability/mass/create"
    When I fill in the following:
      | unavailability_mass_create[category]                | 1                              |
      | unavailability_mass_create[name]                    | MyMassUnavailability           |
      | unavailability_mass_create[begin]                   | 10/10/2016 10:10               |
      | unavailability_mass_create[end]                     | 10/10/2016 12:10               |
      | unavailability_mass_create[blocking]                | 0                              |
      | unavailability_mass_create[translations][fr][title] | Mass Unavailability in french  |
      | unavailability_mass_create[translations][en][title] | Mass Unavailability in english |
    And I check "unavailability_mass_create_types_0"
    And I press "form.unavailability_mass_create.children.submit.label"
    Then I should see "flash.admin.unavailability.mass.create.success"
    And I should see "MyMassUnavailability"
    And I should see "Mass Unavailability in french"

  Scenario: I can update a mass unavailability
    Given I am logged as admin
    And I am on this page "/fr/event/1/unavailability/mass/update/1"
    When I fill in the following:
      | unavailability_mass_update[category]                | 1                                      |
      | unavailability_mass_update[name]                    | MyUpdatedMassUnavailability            |
      | unavailability_mass_update[blocking]                | 1                                      |
      | unavailability_mass_update[begin]                   | 12/10/2016 12:10                       |
      | unavailability_mass_update[end]                     | 12/10/2016 14:10                       |
      | unavailability_mass_update[translations][fr][title] | Updated Mass Unavailability in french  |
      | unavailability_mass_update[translations][en][title] | Updated Mass Unavailability in english |
    And I press "form.unavailability_mass_update.children.submit.label"
    Then I should see "flash.admin.unavailability.mass.update.success"
    And I should not see "MyMassUnavailability"
    And I should see "MyUpdatedMassUnavailability"
    And I should see "Updated Mass Unavailability in french"
