@event
@happening
@webinar

Feature: I can add question via the API

  Scenario: I can add a new question
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Happening.yml         |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I send a POST request to "http://asddays-2016.vimeet.proximum/fr/sheet/1/happening/4/webinar/question/add" with body:
        """
            {"questionContent": "Bonjour, comment allez-vous ?"}
        """
    And the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """

  Scenario: I can view a new question
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I send a GET request to "http://asddays-2016.vimeet.proximum/fr/sheet/1/happening/4/webinar/questions"
    Then the JSON node "[0].questionContent" should be equal to the string "Bonjour, comment allez-vous ?"

  Scenario: I can't access questions if happening is not a webinar
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I send a GET request to "http://asddays-2016.vimeet.proximum/fr/sheet/1/happening/1/webinar/questions"
    Then the response status code should be 403
