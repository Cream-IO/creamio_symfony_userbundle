Feature: Create user through POST method
  In order to use the application
  I need to be able to create a new user using the POST method

  Scenario: Create a user
    Given the user table is empty
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/admin/api/users" with body:
    """
    {"username":"TestUser","plain_password":"TestPass","email":"test@test.com","roles":["ROLE_ADMIN"],"first_name":"Test","last_name":"User","job":"Developer","description":"My beautiful description"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the header "Location" should contain "/admin/api/users/"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "201"
    And the JSON node "request-method" should be equal to "POST"

  Scenario: Verify user creation
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/admin/api/users"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "200"
    And the JSON node "request-method" should be equal to "GET"
    And the JSON node "results-for" should be equal to "users-list"
    And the JSON node "results.users[0].email" should be equal to "test@test.com"
    And the JSON should be valid according to the schema "features/references/GET_SCHEMA_users_list.json"

  Scenario: Get 400 error on bad content type
    When I add "Content-Type" header equal to "text/plain"
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/admin/api/users"
    Then the response status code should be 400
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "400"
    And the JSON node "type" should be equal to "Bad Request"
    And the JSON node "reason" should be equal to "Invalid content type, please send application/json content"

  Scenario: Get 400 error on incomplete or invalid body
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/admin/api/users" with body:
    """
    {"username":"TestUser"}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "400"
    And the JSON node "type" should be equal to "Bad Request"
    And the JSON node "reason" should be equal to "Error while validating ressource insertion/update"
    And the JSON should be valid according to the schema "features/references/POST_SCHEMA_users_error.json"