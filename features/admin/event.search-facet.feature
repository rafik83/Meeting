@admin @admin-event @search-facet
Feature: See and update search facet
  I need to be able to see and update search facet for an event catalog

  Scenario: See and update search facet
    Given the database is purged
    And the event "Rdv Carnot 2019" is created
    And I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.search_facets.link"
    Then the response status code should be 200
    And I should see "admin.event.search_facet.title"
    And I should see "français"
    When I fill in the following:
      | search_facet_update_searchFacets_category_translations_fr_label | Catégorie |
      | search_facet_update_searchFacets_category_translations_en_label | Category  |
    And I check "search_facet_update_searchFacets_category_enabled"
    And I press "search_facet_update_submit"
    Then I should see "flash.admin.event.filter_facet.update.success"
    And the "search_facet_update_searchFacets_category_translations_fr_label" field should contain "Catégorie"
    And the "search_facet_update_searchFacets_category_translations_en_label" field should contain "Category"

  Scenario: I can enable search type facet and see them in the catalog
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.search_facets.link"
    Then the response status code should be 200
    When I fill in the following:
      | search_facet_update_searchFacets_type_translations_fr_label | Participants |
      | search_facet_update_searchFacets_type_translations_en_label | Participants |
    And I check "search_facet_update_searchFacets_type_enabled"
    And I press "search_facet_update_submit"
    Then I should see "flash.admin.event.filter_facet.update.success"

# move this test in event fetaure ?
  # Scenario: I can see search type in catalog
  #   When I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum"
  #   And I go to this page "/fr"
  #   Then I should be on this page "/fr/sheet/1"
  #   And I should see "Les rendez-vous CARNOT 2016"
  #   When I follow "navigation.links.catalog.available_date"
  #   Then I should see "Participants"
