@admin @sheet @mail

Feature: Edit participant status
  As an admin, I can edit participant status

  Scenario: I can validate participant registration
    Given the database is purged
    And the event "Paris Fashion Week" is created
    And the domain for this event is "fashionweeek.vimeet.proximum"
    And there is a sheet with the title "YSL"
    And there is a participant for this sheet
    And elastica is populate
    And I am logged as admin
    And I go to "/fr/event"
    Then I go to "/fr/event/1/sheet"
    And I should see "admin.sheet.title.count"
    Then I check "sheet_batch_ids_1"
    And I press "form.sheet_batch.children.validate.label"

  Scenario: I can enable or disable participant registration
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_1"
    When I press "form.sheet_batch.children.disable.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.disable.success"

  Scenario: I can add or remove a sheet from the catalog
    Given I am logged as admin
    And there is an event with domain "fashionweeek.vimeet.proximum"
    And there is a sheet with the title "Lanvin"
    And elastica is populate
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_2"
    When I press "form.sheet_batch.children.addCatalog.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "admin.sheet.inCatalog" in the "#sheet-2" element

  Scenario: I can put sheet participation content to validated
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_2"
    When I press "form.sheet_batch.children.validationStateValidate.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.validation.validate.success"
    And I should see "event.sheet.validationState.validated" in the "#sheet-2" element

  Scenario: I can put sheet participation to draft
    And I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_2"
    When I press "form.sheet_batch.children.validationStateDraft"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.draft.success"
    And I should see "event.sheet.validationState.draft" in the "#sheet-2" element

  Scenario: I can use filters, navigate on admin and see my filters was saved
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet?type=1&page=1&validationState=draft&state=pending&enabled=1&orderBy=createdAt"
    Then I should see "form.sheet_filter.children.enabled.label"
    And I should see "form.sheet_filter.children.state.label"
    And I should see "form.sheet_filter.children.type.label"
    And I should see "form.sheet_filter.children.validationState.label"
    And I should see "form.sheet_filter.children.orderBy.label"
    When I go to this page "/fr/event"
    And I go to "/fr/event/1/sheet"
    Then I should be on this page "/fr/event/1/sheet?type=1&page=1&validationState=draft&state=pending&enabled=1&orderBy=createdAt"
    And I should see "form.sheet_filter.children.enabled.label"
    And I should see "form.sheet_filter.children.state.label"
    And I should see "form.sheet_filter.children.type.label"
    And I should see "form.sheet_filter.children.validationState.label"
    And I should see "form.sheet_filter.children.orderBy.label"

  Scenario: I should see a warning message on remove from catalog batch when some sheets have scheduled meetings
    Given I am logged as admin
    And there is an event with domain "fashionweeek.vimeet.proximum"
    And there is a sheet with the title "Vogue"
    And this sheet is in catalog
    And there is a participant for this sheet
    And there is an active spot "A1" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is a meeting slot from 2020-10-12 08:00:00 to 2020-10-12 08:30:00
    And there is a meeting between "Vogue" and "YSL" on spot "A1"
    And elastica is populate
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet?text=vogue&enabled=1&orderBy=created_at"
    And I check "sheet_batch_ids_3"
    And I press "form.sheet_batch.children.removeCatalog"
    Then I should be on this page "/fr/event/1/sheet?text=vogue&enabled=1&orderBy=created_at"
    And I should see "flash.admin.sheet_batch.catalog.remove.warning"

  Scenario: I should see a warning message on disable batch when some sheets have scheduled meetings
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet?text=vogue&enabled=1&orderBy=created_at"
    And I check "sheet_batch_ids_3"
    And I press "form.sheet_batch.children.disable"
    Then I should be on this page "/fr/event/1/sheet?text=vogue&enabled=1&orderBy=created_at"
    And I should see "flash.admin.sheet_batch.disable.warning"

  Scenario: I should see a warning message on add to catalog batch when some sheets are disabled
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet?enabled="
    And I check "sheet_batch_ids_1"
    And I press "form.sheet_batch.children.addCatalog.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.catalog.add.warning"
