<?php

declare(strict_types=1);

namespace Tests\Providers\Test;

trait TestProviderTrait
{
    /**
     * @return array<string, array{
     *     capsule: string,
     *     entity: string,
     *     properties: array<int, string>,
     *     files: array<int, string>,
     *     interfaces: array<class-string, mixed>
     * }>
     */
    public static function assertCapsuleProvider(): array
    {
        return [
            'case-0' => [
                'capsule' => 'Users',
                'entity' => 'users',
                'properties' => [
                    'idusers:int',
                    'users_name:string',
                ],
                'files' => [
                    'database/Class/Users.php',
                    'app/Interfaces/Database/Class/Users/IdusersInterface.php',
                    'app/Interfaces/Database/Class/Users/UsersNameInterface.php',
                ],
                'interfaces' => [
                    'App\\Interfaces\\Database\\Class\\Users\\IdusersInterface' => [
                        'column' => 'idusers',
                        'set' => 1,
                        'get' => 1,
                    ],
                    'App\\Interfaces\\Database\\Class\\Users\\UsersNameInterface' => [
                        'column' => 'users_name',
                        'set' => 'test name',
                        'get' => 'test name',
                    ],
                ],
            ],
            'case-1' => [
                'capsule' => 'ParkingOccupations',
                'entity' => 'parking_occupations',
                'properties' => [
                    'idparking_occupations:int',
                    'parking_occupations_name:string',
                ],
                'files' => [
                    'database/Class/ParkingOccupations.php',
                    'app/Interfaces/Database/Class/ParkingOccupations/IdparkingOccupationsInterface.php',
                    'app/Interfaces/Database/Class/ParkingOccupations/ParkingOccupationsNameInterface.php',
                ],
                'interfaces' => [
                    'App\\Interfaces\\Database\\Class\\ParkingOccupations\\IdparkingOccupationsInterface' => [
                        'column' => 'idparking_occupations',
                        'set' => 1,
                        'get' => 1,
                    ],
                    'App\\Interfaces\\Database\\Class\\ParkingOccupations\\ParkingOccupationsNameInterface' => [
                        'column' => 'parking_occupations_name',
                        'set' => 'name',
                        'get' => 'name',
                    ],
                ],
            ],
            'case-2' => [
                'capsule' => 'ParkingOccupationsExample',
                'entity' => 'parking_occupations_example',
                'properties' => [
                    'idparking_occupations_example:int',
                    'parking_occupations_example_name:string',
                ],
                'files' => [
                    'database/Class/ParkingOccupationsExample.php',
                    'app/Interfaces/Database/Class/ParkingOccupationsExample/IdparkingOccupationsExampleInterface.php',
                    'app/Interfaces/Database/Class/ParkingOccupationsExample/ParkingOccupationsExampleNameInterface.php', // phpcs:ignore
                ],
                'interfaces' => [
                    'App\\Interfaces\\Database\\Class\\ParkingOccupationsExample\\IdparkingOccupationsExampleInterface' => [ // phpcs:ignore
                        'column' => 'idparking_occupations_example',
                        'set' => 1,
                        'get' => 1,
                    ],
                    'App\\Interfaces\\Database\\Class\\ParkingOccupationsExample\\ParkingOccupationsExampleNameInterface' => [ // phpcs:ignore
                        'column' => 'parking_occupations_example_name',
                        'set' => 'name',
                        'get' => 'name',
                    ],
                ],
            ],
        ];
    }
}
