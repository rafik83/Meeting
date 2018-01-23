@admin
@admin-event
Feature: Update terms of sale
  I need to be able to update the terms of sale of my event

  Scenario: Update terms of sale
    Given the database is purged
    And the event "Terms And Conditions Party" is created
    And there is terms of sale for this event
    And the super admin "test@test.com" is created
    And I am logged with this admin
    And I am on the homepage of the admin
    When I go to this page "/en/event"
    And I follow "admin.update_content.terms-of-sale.link"
    Then I should be on this page "/en/event/1/content/terms-of-sale/update"
    When I fill in the following:
      | content_update_translations_fr_value | Bla Bla Bla  |
      | content_update_translations_en_value | Foo Bar Foo  |
    And I press "form.content_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.content.update_terms-of-sale.success"
    And I should be on this page "/en/event/1/content/terms-of-sale/update"
