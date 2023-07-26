<?php
// get_aws_credentials.php

// Function to get AWS credentials
function getAwsCredentials() {
  $awsCredsFile = 'awscreds.json';

  // Read the JSON file and parse its contents
  $jsonContents = file_get_contents($awsCredsFile);
  $credentials = json_decode($jsonContents, true);

  return $credentials;
}

// Return the AWS credentials as JSON response
header('Content-Type: application/json');
echo json_encode(getAwsCredentials());
?>
