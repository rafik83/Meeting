@admin @admin-event

Feature: Add extra parameters
  I need to add extra parameter for an event.

  @leniBadge
  Scenario: Add LENI badge link parameter
    Given the database is purged
    And the event "Les rendez-vous CARNOT 2016" is created
    And the super admin "test@test.com" is created
    And I am logged with this admin
    When I go to this page "/fr/event/1/extra/parameter/list"
    And I follow "admin.extraParameter.list.create"
    Then I select "form.extra_parameter_create.children.type.choices.leni_badge_link" from "extra_parameter_create_type"
    And I fill in "extra_parameter_create_name" with "Leni badge link"
    And I fill in "extra_parameter_create_value" with "{\"link\": \"https://localhost/leni/%s/view\", \"concerned_type_ids\": [123,595]}"
    And I press "form.extra_parameter_create.children.submit.label"
    Then I should be on this page "/fr/event/1/extra/parameter/list"
    And I should see "Leni badge link"
    And I should see "{\"link\": \"https://localhost/leni/%s/view\", \"concerned_type_ids\": [123,595]}"
