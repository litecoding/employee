<?php

namespace App\Tests\Controller;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EmployeeControllerTest extends WebTestCase
{
    private function createEmployee($client): int
    {
        $client->request(
            'POST',
            '/api/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                $this->generateEmployeeData()
            )
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        return $responseData['id'];
    }

    public function testCreateEmployee(): void
    {
        $client = static::createClient();

        $employeeId = $this->createEmployee($client);

        $this->assertNotNull($employeeId);
    }

    public function testGetEmployee(): void
    {
        $client = static::createClient();

        $employeeId = $this->createEmployee($client);

        $client->request('GET', '/api/employees/' . $employeeId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testListEmployees(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/employees');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testUpdateEmployee(): void
    {
        $client = static::createClient();

        $employeeId = $this->createEmployee($client);

        $client->request(
            'PUT',
            '/api/employees/' . $employeeId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($this->generateEmployeeData())
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteEmployee(): void
    {
        $client = static::createClient();

        $employeeId = $this->createEmployee($client);

        $client->request('DELETE', '/api/employees/' . $employeeId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private function generateEmployeeData(): array
    {
        $faker = Factory::create();
        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->unique()->safeEmail;
        $hiredAt = (new \DateTime())->format('Y-m-d');
        $salary = $faker->numberBetween(100, 5000);

        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'hiredAt' => $hiredAt,
            'salary' => $salary
        ];
    }
}
