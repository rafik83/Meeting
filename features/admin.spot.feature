Feature: Create spot
  I need to be able to create a spot

  Scenario: I can create spot
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    And I follow "admin.spot.link"
    Then the response status code should be 200
    And I follow "admin.spot.listSpot.add"
    Then the response status code should be 200
    And I should be on "http://vimeet.proximum.dev/app_test.php/admin/event/1/spot/create"
    And I fill in the following:
      | spot_create_reference       | D098 |
      | spot_create_size            | 3    |
      | spot_create_meetingCapacity | 6    |
      | spot_create_seatCapacity    | 2    |
    And I check "spot_create_active"
    And I press "form.spot_create.children.submit.label"
    Then the response status code should be 200
    And I should be on "http://vimeet.proximum.dev/app_test.php/admin/event/1/spot"
    And I should see "flash.admin.spot.create.success"

  Scenario: I can list spot
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | Spot.yml                                                  |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "admin.spot.link"
    Then the response status code should be 200
    And I should see "G0345"

  Scenario: I can filter out spot with just one filter (reference)
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | Spot.yml                                                  |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "admin.spot.link"
    Then the response status code should be 200
    And I fill in the following:
      | form.admin.filter_spot_type.children.reference.label | A |
    And I check "form.admin.filter_spot_type.children.active.label"
    And I press "form.admin.filter_spot_type.children.submit.label"
    Then the response status code should be 200
    And I should see "A008"
    And I should see "A100"

  Scenario: I can filter out spot with just one other filter (meetingCapacity)
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | Spot.yml                                                  |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "admin.spot.link"
    Then the response status code should be 200
    And I fill in the following:
      | form.admin.filter_spot_type.children.meetingCapacity.label | 5 |
    And I check "form.admin.filter_spot_type.children.active.label"
    And I press "form.admin.filter_spot_type.children.submit.label"
    Then the response status code should be 200
    And I should see "F098"
    And I should see "B090"

  Scenario: I can filter out spot with all champ fill in
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | Spot.yml                                                  |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "admin.spot.link"
    Then the response status code should be 200
    And I fill in the following:
      | form.admin.filter_spot_type.children.reference.label       | A |
      | form.admin.filter_spot_type.children.meetingCapacity.label | 3 |
      | form.admin.filter_spot_type.children.seatCapacity.label    | 4 |
      | form.admin.filter_spot_type.children.size.label            | 6 |
    And I check "form.admin.filter_spot_type.children.active.label"
    And I press "form.admin.filter_spot_type.children.submit.label"
    Then the response status code should be 200
    And I should see "A008"
