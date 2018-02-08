@admin @sheet @filter

Feature: Sheet participations list filters
  As an admin, I can filters the sheet participations list

  Scenario: I can use advanced filters
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
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
    When I go to this page "/fr/event/past"
    When I follow "admin.sheet.link"
    Then I should be on this page "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"

  Scenario: I can filter sheet by enabled state
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check the "form.sheet_filter.children.enabledState.enabled.label" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "World Company Inc"
    And I should see "Aanera"
    When I check the "form.sheet_filter.children.enabledState.disabled.label" radio
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "Hello World Company"
    And I should not see "World Company Inc"
    And I should not see "Aanera"

  Scenario: I can filter sheet by validation state
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check "event.sheet.validationState.draft"
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "World Company Inc"
    And I should see "Aanera"
    When I check "event.sheet.validationState.pending"
    And I uncheck "event.sheet.validationState.draft"
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "Hello World Company"
    And I should not see "World Company Inc"
    And I should not see "Aanera"

  Scenario: I can filter sheet by state
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check "event.sheet.state.pending"
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "World Company Inc"
    And I should see "Aanera"
    When I check "event.sheet.state.accepted"
    And I uncheck "event.sheet.state.pending"
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "Hello World Company"
    And I should not see "World Company Inc"
    And I should not see "Aanera"

  Scenario: I can filter sheet by completeness
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check the "event.sheet.completed.incomplete" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "World Company Inc"
    And I should see "Aanera"
    When I check the "event.sheet.completed.complete" radio
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "Hello World Company"
    And I should not see "World Company Inc"
    And I should not see "Aanera"

  Scenario: I can filter sheet in catalog
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check the "boolean.yes" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "World Company Inc"
    And I should see "Aanera"
    When I check the "boolean.no" radio
    And I press "form.admin.sheet.filter.children.submit.label"
    And I should not see "Hello World Company"
    And I should not see "World Company Inc"
    And I should not see "Aanera"

  Scenario: I can filter sheet by type
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check "Fournisseur"
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "Aanera"
    But I should not see "World Company Inc"
    When I check "Investisseur"
    And I uncheck "Fournisseur"
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "World Company Inc"
    But I should not see "Hello World Company"
    And I should not see "Aanera"

  Scenario: I can filter sheet has remaining to pay
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check the "form.sheet_filter.children.hasRemainingToPay.no.label" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "Aanera"
    And I should see "World Company Inc"
    When I check the "form.sheet_filter.children.hasRemainingToPay.yes.label" radio
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "World Company Inc"
    And I should not see "Hello World Company"
    And I should not see "Aanera"

  Scenario: I can filter sheet has order superior to zeros
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at&orderBy=created_at&order_status%5B0%5D=no_order&order_status%5B1%5D=total_order_superior_zero&order_status%5B2%5D=total_order_equal_zero"
    And I should see "admin.sheet.list.filters.label"
    And I check the "form.sheet_filter.children.order.no.label" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "Aanera"
    And I should see "World Company Inc"
    When I uncheck "form.sheet_filter.children.order.totalEqualZero.label"
    And I uncheck "form.sheet_filter.children.order.no.label"
    And the "form.sheet_filter.children.order.totalSuperiorZero.label" checkbox should be checked
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "World Company Inc"
    And I should not see "Hello World Company"
    And I should not see "Aanera"

  Scenario: I can filter sheet has cart
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check the "form.sheet_filter.children.cart.no.label" radio
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should see "Hello World Company"
    And I should see "Aanera"
    And I should see "World Company Inc"
    When I check the "form.sheet_filter.children.cart.yes.label" radio
    And I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "World Company Inc"
    And I should not see "Hello World Company"
    And I should not see "Aanera"

  Scenario: I can filter sheet with cancelled attend
    Given I am logged with "test@test.com" on admin
    And I go to "/fr/event/1/sheet?orderBy=created_at"
    And I should see "admin.sheet.list.filters.label"
    And I check radio "cancelAttendance_0"
    When I press "form.admin.sheet.filter.children.submit.label"
    Then I should not see "Hello World Company"
    And I should not see "Aanera"
    And I should not see "World Company Inc"

  Scenario: I can filter sheet with group
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And the user "sheetgroup@example.com" is created
    And there is a group "Sheet Group" managed by this user
    And there is a sheet in this group
    And elastica is populate
    When I go to "/fr/event/1/sheet?orderBy=created_at&hasGroup=1"
    Then I should see "sheetgroup@example.com"
    When I go to "/fr/event/1/sheet?orderBy=created_at&hasGroup=0"
    Then I should not see "sheetgroup@example.com"

