Feature: List sheet
  I can see sheets

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml       |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml        |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Category.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/User.yml        |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Participant.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Sheet.yml       |

  Scenario: I can list sheet of an event
    When I go to "http://vimeet.proximum.dev/app_test.php/admin/event"
    Then the response status code should be 200
    And I follow "Fiches"
    Then the response status code should be 200
    And I should be on "http://vimeet.proximum.dev/app_test.php/admin/event/1/sheet"
    And I should see "Elao"
    And I should see "BIOTECH"
