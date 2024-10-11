<?php

use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase {
    // Access for home page
    public function testHomeRouteIsAccessible() {
        $url = 'http://localhost:8080';

        $response = file_get_contents($url);

        $this->assertNotFalse($response, 'The /home route is not accessible.');

        $headers = get_headers($url);
        $this->assertStringContainsString('200', $headers[0], 'The /home route did not return a 200 OK status.');
    }

    // Access for sign form page
    public function testSignRouteIsAccessible() {
        $urls = [
            'http://localhost:8080/pages/sign_form.php?type=sign_in',
            'http://localhost:8080/pages/sign_form.php?type=sign_up'
        ];

        foreach ($urls as $url) {
            $response = file_get_contents($url); // Fetch the response
            $this->assertNotFalse($response, "The $url route is not accessible."); // Ensure the page is accessible
            // Check for 200 OK status
            $headers = get_headers($url);
            $this->assertStringContainsString('200', $headers[0], "The $url route did not return a 200 OK status.");
        }
    }
}
