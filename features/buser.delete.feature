Feature: Delete user from database
  In order to use the application
  I need to be able to delete an existing user using the DELETE method

  Background: Reset user table before each scenario
    Given the user table is empty

  Scenario: Delete a user with valid UUID
    Given I load a predictable user in database and get it's id
    And I save it into "UserID"
    When I add "Accept" header equal to "application/json"
    And I send a "DELETE" request to "/admin/api/users/<<UserID>>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "200"
    And the JSON node "request-method" should be equal to "DELETE"
    And the JSON node "request-ressource-id" should be equal to "<<UserID>>"

  Scenario: Get 404 error on invalid UUID format
    When I send a "DELETE" request to "/admin/api/users/abcd"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "404"
    And the JSON node "type" should be equal to "Not Found"
    And the JSON node "reason" should be equal to "Invalid id, format must be uuid"

  Scenario: Get 404 error on not existing UUID
    When I send a "DELETE" request to "/admin/api/users/e59c4d7a-89aa-41d0-bc00-da6eee90d78c"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "404"
    And the JSON node "type" should be equal to "Not Found"
    And the JSON node "reason" should be equal to "The resource you have requested can't be found"