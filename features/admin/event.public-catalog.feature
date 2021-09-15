@admin
@admin-event

Feature: Manage event public catalog configuration
  As an admin I need to be able to configure catalog public for an event

  Scenario: Configure event public catalog
    Given the database is purged
    And the event "Super Event" is created
    And the super admin "admin@example.net" is created
    And I am logged with this admin
    When I am on the homepage of the admin
    And I go to this page "/fr/event/1/catalog-external"
    Then I should see "admin.event.catalog.external.configure"
    When I check "externalCatalogEnabled"
    And I press "submit"
    Then I should see "flash.admin.event.catalog.external.configure.success"
    And I should see "http://super-event.vimeet.proximum/fr/list"
    And I should see "http://super-event.vimeet.proximum/en/list"
