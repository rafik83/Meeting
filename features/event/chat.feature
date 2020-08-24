@event
@happening
@webinar
@meeting
@chat

Feature: I can chat

  Scenario: I can participate to webinar chat
    Given the database is purged
    And the event "BestOfWeb" is created
    And the user "hello@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is a webinar in this event
    And this user participate to this happening
    And I am logged with this user
    When I send a POST request to "http://super-event.vimeet.proximum/fr/chat/happening/1/add" with body:
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
    When I send a GET request to "http://super-event.vimeet.proximum/fr/chat/happening/1/list"
    And the JSON node "[0].content" should be equal to the string "Hello, how are you today?"

  Scenario: I can participate to meeting chat
    Given the database is purged
    And the event "BestOfWeb" is created
    And the user "hello2@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is a video meeting for this participant
    And I am logged with this user
    When I send a POST request to "http://super-event.vimeet.proximum/fr/chat/meeting/1/add" with body:
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
    When I send a GET request to "http://super-event.vimeet.proximum/fr/chat/meeting/1/list"
    And the JSON node "[0].content" should be equal to the string "Your product is interesting"

