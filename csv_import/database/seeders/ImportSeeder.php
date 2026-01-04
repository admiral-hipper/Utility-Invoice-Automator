<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disk = Storage::disk('import');
        foreach ($this->files() as $file) {
            $disk->put($file['original_name'], $file['csv']);
            Import::factory([
                'period' => $file['period'],
                'file_path' => $disk->path($file['original_name']),
                'total_rows' => 5,
            ])->create();
        }
    }

    public function files(): array
    {
        return [
            [
                'period' => '2025-10',
                'original_name' => 'Import_2025-10-14-234567.csv',
                'csv' => <<<CSV
first_name,last_name,phone,house_address,apartment,gas,electricity,heating,territory,water,currency
Andrei,Popescu,+40720100101,Str. Lalelelor 12,10,120.50,85.20,210.00,32.00,55.10,RON
Ioana,Ionescu,+40720100102,Str. Lalelelor 12,11,98.30,74.00,190.00,32.00,49.80,RON
Mihai,Stan,+40720100103,Str. Victoriei 7,4,110.00,92.10,230.50,28.00,60.00,RON
Elena,Dumitru,+40720100104,Bd. Revolutiei 23,2,130.75,88.40,205.00,35.00,57.25,RON
Radu,Marin,+40720100105,Str. Unirii 5,8,105.20,79.90,198.00,30.00,52.60,RON
CSV,
            ],
            [
                'period' => '2025-11',
                'original_name' => 'Import_2025-11-20-123444.csv',
                'csv' => <<<CSV
first_name,last_name,phone,house_address,apartment,gas,electricity,heating,territory,water,currency
Andrei,Popescu,+40720100101,Str. Lalelelor 12,10,140.00,90.10,260.00,32.00,58.40,RON
Ioana,Ionescu,+40720100102,Str. Lalelelor 12,11,115.20,80.50,245.00,32.00,51.30,RON
Mihai,Stan,+40720100103,Str. Victoriei 7,4,128.80,96.70,280.50,28.00,62.10,RON
Elena,Dumitru,+40720100104,Bd. Revolutiei 23,2,150.10,92.20,255.00,35.00,59.90,RON
Radu,Marin,+40720100105,Str. Unirii 5,8,120.00,84.00,240.00,30.00,54.80,RON
CSV,
            ],
            [
                'period' => '2025-12',
                'original_name' => 'Import_2025-12-27-123456.csv',
                'csv' => <<<CSV
first_name,last_name,phone,house_address,apartment,gas,electricity,heating,territory,water,currency
Andrei,Popescu,+40720100101,Str. Lalelelor 12,10,160.30,95.40,310.00,32.00,60.00,RON
Ioana,Ionescu,+40720100102,Str. Lalelelor 12,11,132.90,86.10,295.00,32.00,53.20,RON
Mihai,Stan,+40720100103,Str. Victoriei 7,4,145.00,99.80,330.75,28.00,63.70,RON
Elena,Dumitru,+40720100104,Bd. Revolutiei 23,2,170.50,94.60,300.00,35.00,61.10,RON
Radu,Marin,+40720100105,Str. Unirii 5,8,138.40,88.90,290.00,30.00,56.40,RON
CSV,
            ],
        ];
    }
}
