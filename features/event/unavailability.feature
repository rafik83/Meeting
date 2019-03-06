@event @agenda @unavailability
Feature: Unavailability
  As a participant, I can add and remove an unavailability

  Scenario: I can add an unavailability
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml              |
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I go to this page "/fr/sheet/2/agenda"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should not see "unavailability.title"
    And I follow "agenda.unavailability.add"
    And I should be on this page "/fr/sheet/2/agenda/participant/2/unavailability/create"
    And I should see "form.create_unavailability.children.submit.label"
    And I should see "agenda.unavailability.back"
    Then I fill in the following:
      #mercredi 12 octobre 2016
      | form.create_unavailability.children.day.label | 0   |
      | create_unavailability_time_begin_hour         | 11  |
      | create_unavailability_time_begin_minute       | 30  |
      | create_unavailability_time_end_hour           | 13  |
      | create_unavailability_time_end_minute         | 45  |
    And I press "form.create_unavailability.children.submit.label"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "unavailability.title"

  Scenario: I can remove an unavailability
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/2/agenda"
    And I should see "unavailability.title"
    When I press "cancelUnavailability"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should not see "unavailability.title"

  Scenario: I can add comment to an unavailability
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I go to this page "/fr/sheet/2/agenda"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "agenda.title"
    And I should see "Mercredi 12 octobre 2016"
    And I should not see "unavailability.title"
    And I follow "agenda.unavailability.add"
    And I should be on this page "/fr/sheet/2/agenda/participant/2/unavailability/create"
    And I should see "form.create_unavailability.children.submit.label"
    And I should see "agenda.unavailability.back"
    Then I fill in the following:
      #mercredi 12 octobre 2016
      | form.create_unavailability.children.day.label | 0   |
      | create_unavailability_time_begin_hour         | 11  |
      | create_unavailability_time_begin_minute       | 30  |
      | create_unavailability_time_end_hour           | 13  |
      | create_unavailability_time_end_minute         | 45  |
      | create_unavailability_message                 | "Concert de Patrick sebastien" |
    And I press "form.create_unavailability.children.submit.label"
    Then I should be on this page "/fr/sheet/2/agenda/participant/2"
    And I should see "unavailability.title"
    And I should see "Concert de Patrick sebastien"
