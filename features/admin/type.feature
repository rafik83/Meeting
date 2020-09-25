@admin

Feature: add type
  I need to be able to add a type

  Scenario: add a type
    Given the database is purged
    And the event "RDV Carnot" is created
    And the domain for this event is "rdvcarnot.vimeet.proximum"
    And there is a type in this event
    And there is a sheet for this type with the title "Proximum"
    And I am logged as admin
    And I am on this page "/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I follow "admin.type.create.link"
    Then the response status code should be 200
    And I fill in the following:
      | type_create_sheetTemplate         | 0    |
      | type_create_registrationTemplate  | 0    |
      | type_create_package               | 0    |
      | type_create_translations_fr_title | Test |
      | type_create_translations_en_title | Test |
      | type_create_rank                  | 1    |
    And I check "type_create_hidden"
    And I press "form.type_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.create.success"

  Scenario: edit a type that already has sheet
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I follow "admin.type.update.link"
    Then I should be on "/fr/event/1/type/1/update"
    And I should see "admin.type.update.title"
    And I should not see "form.type_update.sheetTemplate.label"
    And I should not see "form.type_update.registrationTemplate.label"
    And I should not see "form.type_update.package.label"
    And I fill in the following:
      | type_update_translations_fr_title | WithSheetTestEdited |
      | type_update_translations_en_title | WithSheetTestEdited |
      | type_update_rank                  | 2                   |
    When I press "form.type_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.update.success"

  Scenario: edit a type that hasn't sheet right now
    Given there is an event with domain "rdvcarnot.vimeet.proximum"
    And there is a type "Without sheet" in this event
    And this type is hidden
    And I am logged as admin
    And I am on this page "/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    And I should see "Without sheet"
    When I follow edit for type "Without sheet"
    Then I should see "admin.type.update.title"
    And I should see "form.type_update.children.sheetTemplate.label"
    And I should see "form.type_update.children.registrationTemplate.label"
    And I should see "form.type_update.children.package.label"
    And I fill in the following:
      | type_update_translations_fr_title | TestEditedTwo |
      | type_update_translations_en_title | TestEditedTwo |
      | type_update_rank                  | 2             |
    And the "type_update_hidden" checkbox should be checked
    When I press "form.type_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.type.update.success"
    And I should not see "Without sheet"

  Scenario: remove a type that hasn't sheet right now
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    And I follow delete for type "TestEditedTwo"
    Then I should be on "/fr/event/1/type"
    And I should see "flash.admin.type.remove.success"
    And I should not see "TestEditedTwo"

  Scenario: I try to remove a type that has a sheet
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I follow "admin.type.link"
    Then I should be on "/fr/event/1/type"
    When I follow delete for type "WithSheetTestEdited"
    Then I should be on "/fr/event/1/type"
    And I should see "flash.admin.type.remove.error"
    And I should see "WithSheetTestEdited"

  Scenario: I should not see an hidden type
    Given there is an event with domain "rdvcarnot.vimeet.proximum"
    And there is a type "Investisseur" in this event
    And this type is hidden
    When I follow "admin.event.see_on_front.link"
    Then I should not see "Investisseur"

  Scenario: I see type list with associated template
    Given there is an event with domain "rdvcarnot.vimeet.proximum"
    And there is a type "Investisseur" in this event
    And there is a package "Package RDV Carnot" for this event
    And this package is assigned to this type
    And I am logged as admin
    And I am on this page "/fr/event"
    When I go to this page "/fr/event/1/type"
    Then I should see "admin.type.list.title"
    And I should see "Investisseur"
    And I should see "SheetTemplate"
    And I should see "RegistrationTemplate"
    And I should see "Package RDV Carnot"
