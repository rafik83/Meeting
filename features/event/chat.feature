@event
@happening
@webinar
@meeting
@chat

Feature: I can chat

  Scenario: I can participate to webinar chat
    Given the database is purged
    And the event "BestOfWeb" is created
    And the user is created with email "hello@example.net", firstname "Paul" and lastname "DURAND"
    And there is a sheet with the title "World Company"
    And there is a participant for this sheet and this user
    And there is a webinar in this event
    And this user participate to this happening
    And I am logged with this user
    When I send a POST request to "http://super-event.vimeet.proximum/fr/sheet/1/chat/happening/1/add" with body:
      """
          {"content": "Hello, how are you today?"}
      """
    Then print the corresponding curl command
    And the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """
    When I send a GET request to "http://super-event.vimeet.proximum/fr/sheet/1/chat/happening/1/list"
    And the JSON node "[0].content" should be equal to the string "Hello, how are you today?"
    And the JSON node "[0].authorName" should be equal to the string "Paul DURAND"
    And the JSON node "[0].sheetTitle" should be equal to the string "World Company"

  Scenario: I can participate to meeting chat
    Given the database is purged
    And the event "BestOfWeb" is created
    And the user is created with email "vandamme@example.net", firstname "Van" and lastname "DAMME"
    And there is a sheet with the title "Notflix"
    And there is a participant for this sheet and this user
    And there is a video meeting for this participant
    And I am logged with this user
    When I send a POST request to "http://super-event.vimeet.proximum/fr/sheet/1/chat/meeting/1/add" with body:
      """
          {"content": "Your product is interesting"}
      """
    Then print the corresponding curl command
    And the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """
    When I send a GET request to "http://super-event.vimeet.proximum/fr/sheet/1/chat/meeting/1/list"
    And the JSON node "[0].content" should be equal to the string "Your product is interesting"
    And the JSON node "[0].authorName" should be equal to the string "Van DAMME"
    And the JSON node "[0].sheetTitle" should be equal to the string "Notflix"
