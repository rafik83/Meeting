@admin
@admin-event
Feature: See and update search facet
  I need to be able to see and update search facet for an event catalog

  Scenario: See and update search facet
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event/1"
    When I follow "admin.spot.search_facets.link"
    Then the response status code should be 200
    And I should see "admin.event.search_facet.title"
    And I should see "français"
    When I fill in the following:
      | search_facet_translation.children.label.label       | Groupements                    |
      | search_facet_translation.children.placeholder.label | Votre recherche par groupement |
    And I check "search_facet_update_searchFacets_0_enabled"
    And I press "search_facet_update_submit"
    Then I should see "flash.admin.event.filter_facet.update.success"
    And the "search_facet_translation.children.label.label" field should contain "Groupements"
    And the "search_facet_translation.children.placeholder.label" field should contain "Votre recherche par groupement"
