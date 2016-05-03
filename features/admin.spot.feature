Feature: Spot feature
  I need to be able to create, list, filter, a spot and disable and remove them in batch

  Scenario: I can list spot
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | Spot.yml         |
      | Admin.yml        |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event/1/spot"
    Then I should see "G0345"
    And I should see "A008"
    And I should see "B090"
    And I should see "A100"
    And I should see "F098"

  Scenario: I can create spot
    Given I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event/1"
    And I follow "admin.spot.link"
    Then I should be on this page "/admin/fr/event/1/spot"
    When I follow "admin.spot.listSpot.add"
    Then I should be on this page "/admin/fr/event/1/spot/create"
    When I fill in the following:
      | spot_create_reference       | D098 |
      | spot_create_size            | 3    |
      | spot_create_meetingCapacity | 6    |
      | spot_create_seatCapacity    | 2    |
    And I check "spot_create_active"
    And I press "form.spot_create.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/spot"
    And I should see "flash.admin.spot.create.success"

  Scenario: I can filter spots with active filter
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot"
    And I should see "A008"
    And I should see "F098"
    When I follow "admin.spot.active.yes"
    Then I should see "A008"
    And I should not see "F098"

  Scenario: I can filter spots with reference filter
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot"
    When I fill in the following:
      | form.filter_spot_type.children.reference.label | A |
    And I press "submit"
    Then I should see "A008"
    And I should see "A100"
    And I should not see "G0345"
    And I should not see "B090"
    And I should not see "F098"

  Scenario: I can filter spots with meetingCapacity filter
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot"
    When I fill in the following:
      | form.filter_spot_type.children.meetingCapacity.label | 5 |
    And I press "submit"
    Then I should see "F098"
    And I should see "B090"
    And I should not see "A100"
    And I should not see "A008"
    And I should not see "G0345"

  Scenario: I can filter spots using all filters
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot?active=0"
    When I fill in the following:
      | form.filter_spot_type.children.reference.label       | F09 |
      | form.filter_spot_type.children.size.label            | 6   |
      | form.filter_spot_type.children.meetingCapacity.label | 5   |
      | form.filter_spot_type.children.seatCapacity.label    | 4   |
    And I press "submit"
    Then I should see "F098"
    And I should not see "A008"
    And I should not see "A100"
    And I should not see "G0345"
    And I should not see "B090"

  Scenario: I can disable spot in batch mode
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot?active=1"
    And I should see "G0345"
    And I should see "A008"
    And I should see "B090"
    When I check "batch_checkbox_1"
    And I check "batch_checkbox_2"
    And I press "admin.spot.disable"
    Then I should see "flash.admin.spot_batch.disable.success"
    And I go to this page "/admin/fr/event/1/spot?active=0"
    And I should see "G0345"
    And I should see "A008"
    And I should not see "B090"

  Scenario: I can delete spot in batch mode
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/spot"
    And I should see "G0345"
    And I should see "A008"
    And I should see "B090"
    When I check "batch_checkbox_1"
    And I check "batch_checkbox_2"
    And I press "admin.spot.delete"
    Then I should see "flash.admin.spot_batch.delete.success"
    And I should not see "G0345"
    And I should not see "A008"
    And I should see "B090"
