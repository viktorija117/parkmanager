<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Office;
use App\Models\ParkingSpace;
use App\Models\Reservation;
use App\Models\PermanentReservation;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Corp']);
        $office = Office::factory()->create([
            'company_id' => $company->id,
            'name' => 'HQ Belgrade',
        ]);

        foreach (['A', 'B'] as $row) {
            for ($i = 1; $i <= 10; $i++) {
                ParkingSpace::factory()->create([
                    'office_id' => $office->id,
                    'label' => sprintf('%s-%02d', $row, $i),
                    'row' => "Row $row",
                ]);
            }
        }

        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@parkmanager.test',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $users = User::factory()->count(10)->create([
            'company_id' => $company->id,
            'password' => bcrypt('password'),
        ]);

        $permanentSpaces = ParkingSpace::whereIn('label', ['A-01', 'A-02', 'A-03', 'A-04'])->get();
        foreach ($permanentSpaces as $i => $space) {
            PermanentReservation::factory()->create([
                'user_id' => $users[$i]->id,
                'parking_space_id' => $space->id,
                'admin_notes' => 'Long-term assignment',
                'assigned_by' => $admin->id,
            ]);
        }

        $availableSpaces = ParkingSpace::whereNotIn('label', ['A-01', 'A-02', 'A-03', 'A-04'])->get();
        $usedSpaceDate = [];
        foreach ($users as $user) {
            foreach (range(0, 10) as $day) {
                if (rand(0, 1)) {
                    $date = today()->addDays($day);
                    if ($date->isWeekend()) continue;
                    $dateStr = $date->format('Y-m-d');
                    $freeSpaces = $availableSpaces->filter(
                        fn($s) => !isset($usedSpaceDate["{$s->id}:{$dateStr}"])
                    );
                    if ($freeSpaces->isEmpty()) continue;
                    $space = $freeSpaces->random();
                    $usedSpaceDate["{$space->id}:{$dateStr}"] = true;
                    Reservation::factory()->create([
                        'user_id' => $user->id,
                        'parking_space_id' => $space->id,
                        'date' => $dateStr,
                    ]);
                }
            }
        }
    }
}
