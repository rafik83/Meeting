@event
@happening
@webinar

Feature: I can add question via the API

  Scenario: I can add a new question
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet with the title "Test User"
    And there is a participant for this sheet and this user
    And the happenings are open
    And there is a webinar in this event
    And this user participate to this happening
    And I am logged with "user_asddays_1@proximum.com" on front
    When I send a POST request to "http://asddays.vimeet.proximum/fr/sheet/1/happening/1/webinar/question/add" with body:
        """
            {"questionContent": "Bonjour, comment allez-vous ?"}
        """
    Then the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """
    And I wait 1 second
    When I send a POST request to "http://asddays.vimeet.proximum/fr/sheet/1/happening/1/webinar/question/add" with body:
        """
            {"questionContent": "Hello, comment allez-vous ?"}
        """
    Then the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """
    And the user "user_asddays_2@proximum.com" is created
    And there is a sheet with the title "Test User2"
    And there is a participant for this sheet and this user
    And this user participate to this happening
    And I am logged with "user_asddays_2@proximum.com" on front
    When I send a POST request to "http://asddays.vimeet.proximum/fr/sheet/2/happening/1/webinar/question/vote" with body:
        """
            {"questionId": 1}
        """
    Then the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """

  Scenario: I can view questions sorted by date
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I send a GET request to "http://asddays.vimeet.proximum/fr/sheet/1/happening/1/webinar/questions?orderBy=date"
    Then the JSON node "[0].questionContent" should be equal to the string "Hello, comment allez-vous ?"
    Then the JSON node "[1].questionContent" should be equal to the string "Bonjour, comment allez-vous ?"

  Scenario: I can view questions sorted by like
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I send a GET request to "http://asddays.vimeet.proximum/fr/sheet/1/happening/1/webinar/questions?orderBy=like"
    Then the JSON node "[0].questionContent" should be equal to the string "Bonjour, comment allez-vous ?"
    Then the JSON node "[1].questionContent" should be equal to the string "Hello, comment allez-vous ?"

  Scenario: I can't access questions if happening is not a webinar
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And there is a webinar in this event
    When I send a GET request to "http://asddays.vimeet.proximum/fr/sheet/1/happening/2/webinar/questions?orderBy=date"
    Then the response status code should be 403
