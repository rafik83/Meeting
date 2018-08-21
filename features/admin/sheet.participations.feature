@admin @sheet @mail

Feature: Edit participant status
  As an admin, I can edit participant status

  Scenario: I can validate participant registration
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Spot.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-MeetingSlot.yml       |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/ASDDays2016-Meeting.yml   |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I go to "/fr/event"
    Then I go to "/fr/event/1/sheet"
    And I should see "admin.sheet.title.count"
    Then I check "sheet_batch_ids_4"
    And I press "form.sheet_batch.children.validate.label"

  Scenario: I can enable or disable participant registration
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_4"
    When I press "form.sheet_batch.children.disable.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.disable.success"

  Scenario: I can add or remove a sheet from the catalog
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_7"
    When I press "form.sheet_batch.children.addCatalog.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "admin.sheet.inCatalog" in the "#sheet-7" element

  Scenario: I can put sheet participation content to validated
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_7"
    When I press "form.sheet_batch.children.validationStateValidate.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.validation.validate.success"
    And I should see "event.sheet.validationState.validated" in the "#sheet-7" element

  Scenario: I can put sheet participation to draft
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet"
    Then I should see "admin.sheet.title.count"
    And I check "sheet_batch_ids_7"
    When I press "form.sheet_batch.children.validationStateDraft"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.draft.success"
    And I should see "event.sheet.validationState.draft" in the "#sheet-7" element

  Scenario: I can use filters, navigate on admin and see my filters was saved
    Given I am logged with "test@test.com" on admin
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
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/2/sheet?text=aanera&enabled=1&orderBy=created_at"
    And I check "sheet_batch_ids_1"
    And I press "form.sheet_batch.children.removeCatalog"
    Then I should be on this page "/fr/event/2/sheet?text=aanera&enabled=1&orderBy=created_at"
    And I should see "flash.admin.sheet_batch.catalog.remove.warning"

  Scenario: I should see a warning message on disable batch when some sheets have scheduled meetings
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/2/sheet?text=aanera&enabled=1&orderBy=created_at"
    And I check "sheet_batch_ids_1"
    And I press "form.sheet_batch.children.disable"
    Then I should be on this page "/fr/event/2/sheet?text=aanera&enabled=1&orderBy=created_at"
    And I should see "flash.admin.sheet_batch.disable.warning"

  Scenario: I should see a warning message on add to catalog batch when some sheets are disabled
    Given I am logged with "test@test.com" on admin
    And I am on this page "/fr/event"
    When I go to "/fr/event/1/sheet?enabled="
    And I check "sheet_batch_ids_4"
    And I press "form.sheet_batch.children.addCatalog.label"
    Then I should be on this page "/fr/event/1/sheet"
    And I should see "flash.admin.sheet_batch.catalog.add.warning"
