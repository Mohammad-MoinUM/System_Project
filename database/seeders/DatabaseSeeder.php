<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin User ─────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@haalchaal.test'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'onboarding_completed' => true,
            ]
        );

        // ── Test Customer ──────────────────────────────
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ── Service Categories & Providers ─────────────
        $categories = [
            'Cleaning' => [
                ['name' => 'Home Deep Cleaning', 'price' => 1500],
                ['name' => 'Office Cleaning', 'price' => 2500],
                ['name' => 'Kitchen Cleaning', 'price' => 800],
            ],
            'Plumbing' => [
                ['name' => 'Pipe Repair', 'price' => 500],
                ['name' => 'Bathroom Fitting', 'price' => 3000],
                ['name' => 'Water Heater Installation', 'price' => 2000],
            ],
            'Electrical' => [
                ['name' => 'Wiring Repair', 'price' => 600],
                ['name' => 'Fan & Light Installation', 'price' => 400],
                ['name' => 'Full House Wiring', 'price' => 8000],
            ],
            'Painting' => [
                ['name' => 'Room Painting', 'price' => 3500],
                ['name' => 'Exterior Painting', 'price' => 12000],
            ],
            'AC Repair' => [
                ['name' => 'AC Servicing', 'price' => 1200],
                ['name' => 'AC Installation', 'price' => 2500],
                ['name' => 'Gas Refill', 'price' => 1800],
            ],
            'Carpentry' => [
                ['name' => 'Furniture Repair', 'price' => 700],
                ['name' => 'Custom Shelving', 'price' => 2500],
            ],
            'Tutoring' => [
                ['name' => 'Math Tutoring', 'price' => 1000],
                ['name' => 'English Tutoring', 'price' => 800],
                ['name' => 'Science Tutoring', 'price' => 1000],
            ],
            'Beauty & Salon' => [
                ['name' => 'Haircut at Home', 'price' => 300],
                ['name' => 'Bridal Makeup', 'price' => 5000],
                ['name' => 'Facial Treatment', 'price' => 600],
            ],
            'Moving & Shifting' => [
                ['name' => 'House Shifting', 'price' => 5000],
                ['name' => 'Office Relocation', 'price' => 10000],
            ],
            'Appliance Repair' => [
                ['name' => 'Refrigerator Repair', 'price' => 1000],
                ['name' => 'Washing Machine Repair', 'price' => 800],
                ['name' => 'Microwave Repair', 'price' => 500],
            ],
        ];

        $providerData = [
            ['first_name' => 'Rahim',   'last_name' => 'Uddin',   'city' => 'Dhaka',      'area' => 'Mirpur',      'expertise' => 'Cleaning',          'experience_years' => 5, 'bio' => 'Professional cleaning expert with 5 years of experience in residential and commercial spaces.'],
            ['first_name' => 'Karim',   'last_name' => 'Hossain', 'city' => 'Dhaka',      'area' => 'Dhanmondi',   'expertise' => 'Plumbing',          'experience_years' => 8, 'bio' => 'Licensed plumber specializing in bathroom fittings and pipe installation.'],
            ['first_name' => 'Jamal',   'last_name' => 'Ahmed',   'city' => 'Chittagong', 'area' => 'Agrabad',     'expertise' => 'Electrical',        'experience_years' => 10, 'bio' => 'Certified electrician with a decade of experience in safe wiring solutions.'],
            ['first_name' => 'Nusrat',  'last_name' => 'Jahan',   'city' => 'Dhaka',      'area' => 'Gulshan',     'expertise' => 'Beauty & Salon',    'experience_years' => 6, 'bio' => 'Professional makeup artist and beauty consultant.'],
            ['first_name' => 'Farhan',  'last_name' => 'Rahman',  'city' => 'Dhaka',      'area' => 'Uttara',      'expertise' => 'AC Repair',         'experience_years' => 4, 'bio' => 'HVAC technician specializing in split and window AC servicing.'],
            ['first_name' => 'Sumon',   'last_name' => 'Mia',     'city' => 'Sylhet',     'area' => 'Zindabazar',  'expertise' => 'Painting',          'experience_years' => 7, 'bio' => 'Interior and exterior painting specialist with an eye for detail.'],
            ['first_name' => 'Ayesha',  'last_name' => 'Begum',   'city' => 'Dhaka',      'area' => 'Banani',      'expertise' => 'Tutoring',          'experience_years' => 3, 'bio' => 'Experienced tutor in Math and Science for secondary and higher secondary levels.'],
            ['first_name' => 'Tanvir',  'last_name' => 'Alam',    'city' => 'Chittagong', 'area' => 'Nasirabad',   'expertise' => 'Carpentry',         'experience_years' => 12, 'bio' => 'Master carpenter — custom furniture and repair work.'],
            ['first_name' => 'Shakil',  'last_name' => 'Khan',    'city' => 'Dhaka',      'area' => 'Mohammadpur', 'expertise' => 'Moving & Shifting', 'experience_years' => 5, 'bio' => 'Reliable house and office shifting service across Dhaka.'],
            ['first_name' => 'Ritu',    'last_name' => 'Das',     'city' => 'Dhaka',      'area' => 'Mirpur',      'expertise' => 'Appliance Repair',  'experience_years' => 6, 'bio' => 'Expert in repairing refrigerators, washing machines, and microwaves.'],
            ['first_name' => 'Mehedi',  'last_name' => 'Hasan',   'city' => 'Dhaka',      'area' => 'Dhanmondi',   'expertise' => 'Cleaning',          'experience_years' => 3, 'bio' => 'Eco-friendly cleaning services for homes and offices.'],
            ['first_name' => 'Limon',   'last_name' => 'Sarker',  'city' => 'Chittagong', 'area' => 'Halishahar',  'expertise' => 'Plumbing',          'experience_years' => 6, 'bio' => 'Quick and reliable plumbing fixes at affordable rates.'],
        ];

        foreach ($providerData as $i => $data) {
            $provider = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => 'provider' . ($i + 1) . '@haalchaal.test',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'phone' => '+8801700' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'city' => $data['city'],
                'area' => $data['area'],
                'expertise' => $data['expertise'],
                'experience_years' => $data['experience_years'],
                'bio' => $data['bio'],
                'onboarding_completed' => true,
            ]);

            // Assign services from their expertise category
            $primaryCategory = $data['expertise'];
            if (isset($categories[$primaryCategory])) {
                foreach ($categories[$primaryCategory] as $svc) {
                    Service::create([
                        'provider_id' => $provider->id,
                        'name' => $svc['name'],
                        'description' => $svc['name'] . ' by ' . $data['first_name'],
                        'category' => $primaryCategory,
                        'price' => $svc['price'],
                        'is_active' => true,
                    ]);
                }
            }

            // Some providers also offer services in a second category
            $secondaryCategories = array_keys($categories);
            $secondaryCategory = $secondaryCategories[($i + 3) % count($secondaryCategories)];
            if ($secondaryCategory !== $primaryCategory && isset($categories[$secondaryCategory])) {
                $secondarySvc = $categories[$secondaryCategory][0];
                Service::create([
                    'provider_id' => $provider->id,
                    'name' => $secondarySvc['name'],
                    'description' => $secondarySvc['name'] . ' by ' . $data['first_name'],
                    'category' => $secondaryCategory,
                    'price' => $secondarySvc['price'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
