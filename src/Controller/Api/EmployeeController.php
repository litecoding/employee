<?php

namespace App\Controller\Api;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Employees")]
class EmployeeController extends AbstractController
{
    #[Route('/api/employees', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new employee",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["firstName", "lastName", "email", "hiredAt", "salary"],
                properties: [
                    new OA\Property(property: "firstName", type: "string"),
                    new OA\Property(property: "lastName", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "hiredAt", type: "string", format: "date"),
                    new OA\Property(property: "salary", type: "number")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Employee created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Employee created successfully"),
                        new OA\Property(property: "id", type: "integer", example: 123)
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Validation errors",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Validation errors")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function createEmployee(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);

        $employee = new Employee();
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);
        $employee->setEmail($data['email']);
        $employee->setHiredAt(new \DateTime($data['hiredAt']));
        $employee->setSalary($data['salary']);

        $errors = $validator->validate($employee);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($employee);
        $entityManager->flush();

        $employeeId = $employee->getId();

        return new JsonResponse(
            ['message' => 'Employee created successfully', 'id' => $employeeId],
            Response::HTTP_CREATED
        );
    }


    #[Route('/api/employees/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Get an employee by ID",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee details"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function getEmployee(int $id, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $employee = $entityManager->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return new JsonResponse(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $employeeData = $serializer->normalize($employee, null, ['groups' => 'employee_details']);

        return new JsonResponse($employeeData);
    }

    #[Route('/api/employees', methods: ['GET'])]
    #[OA\Get(
        summary: "List all employees",
        responses: [
            new OA\Response(response: 200, description: "List of employees")
        ]
    )]
    public function listEmployees(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $employees = $entityManager->getRepository(Employee::class)->findAll();

        $employeesData = $serializer->normalize($employees, null, ['groups' => 'employee_details']);

        return new JsonResponse($employeesData);
    }

    #[Route('/api/employees/{id}', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update an employee",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee updated successfully"),
            new OA\Response(response: 400, description: "Validation errors"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function updateEmployee(int $id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $employee = $entityManager->getRepository(Employee::class)->find($id);
        if (!$employee) {
            return new JsonResponse(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);
        $employee->setEmail($data['email']);
        $employee->setHiredAt(new \DateTime($data['hiredAt']));
        $employee->setSalary($data['salary']);

        $errors = $validator->validate($employee);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'Employee updated successfully']);
    }

    #[Route('/api/employees/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete an employee",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee deleted successfully"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function deleteEmployee(int $id, EntityManagerInterface $entityManager): Response
    {
        $employee = $entityManager->getRepository(Employee::class)->find($id);
        if (!$employee) {
            return new JsonResponse(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($employee);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Employee deleted successfully']);
    }
}