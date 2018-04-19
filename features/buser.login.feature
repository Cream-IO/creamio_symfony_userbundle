Feature: Login to the API
  In order to use the application
  I need to be able to login using POST method

  Background: Reset user table before each scenario
    Given the user table is empty

  Scenario: Get 401 error on unauthenticated call to secured route
    When I send a "GET" request to "/securedTestRoute"
    Then the response status code should be 401
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "401"
    And the JSON node "type" should be equal to "Unauthorized"
    And the JSON node "reason" should be equal to 'You must authenticate to access to this ressource'

  Scenario: Login as a user
    Given I load a predictable user in database and get it's id
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/admin/api/login" with body:
    """
    {
      "username": "testUsername",
      "password": "testPassword"
    }
    """
    Then the response status code should be 200