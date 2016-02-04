Feature: Create spot
  I need to be able to create a spot

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Category.yml |

  Scenario: I can create spot
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    And I follow "Stand"
    Then the response status code should be 200
    And I follow "Ajouter"
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
    Given the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Category.yml |
      | Spot.yml                                                  |
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "Stand"
    Then the response status code should be 200
    And I should see "G0345"
