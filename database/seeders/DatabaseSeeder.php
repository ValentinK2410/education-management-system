<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create([
            'name' => 'Администратор',
            'slug' => 'admin',
            'description' => 'Полный доступ к системе'
        ]);

        $instructorRole = Role::create([
            'name' => 'Преподаватель',
            'slug' => 'instructor',
            'description' => 'Преподаватель курсов'
        ]);

        $studentRole = Role::create([
            'name' => 'Студент',
            'slug' => 'student',
            'description' => 'Студент'
        ]);

        // Create permissions
        $permissions = [
            ['name' => 'Управление пользователями', 'slug' => 'manage_users'],
            ['name' => 'Управление заведениями', 'slug' => 'manage_institutions'],
            ['name' => 'Управление программами', 'slug' => 'manage_programs'],
            ['name' => 'Управление курсами', 'slug' => 'manage_courses'],
            ['name' => 'Просмотр контента', 'slug' => 'view_content'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign permissions to roles
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));
        $instructorRole->permissions()->sync(Permission::whereIn('slug', ['manage_courses', 'view_content'])->pluck('id'));
        $studentRole->permissions()->sync(Permission::where('slug', 'view_content')->pluck('id'));

        // Create admin user
        $admin = User::create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '+7 (999) 123-45-67',
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole);

        // Create instructor
        $instructor = User::create([
            'name' => 'Иван Петров',
            'email' => 'instructor@example.com',
            'password' => Hash::make('password'),
            'phone' => '+7 (999) 234-56-78',
            'bio' => 'Опытный преподаватель с 10-летним стажем',
            'is_active' => true,
        ]);
        $instructor->roles()->attach($instructorRole);

        // Create student
        $student = User::create([
            'name' => 'Анна Сидорова',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'phone' => '+7 (999) 345-67-89',
            'is_active' => true,
        ]);
        $student->roles()->attach($studentRole);

        // Create institutions
        $institutions = [
            [
                'name' => 'Московский государственный университет',
                'description' => 'Ведущий университет России с богатой историей и традициями',
                'address' => 'Москва, Ленинские горы, 1',
                'phone' => '+7 (495) 939-10-00',
                'email' => 'info@msu.ru',
                'website' => 'https://www.msu.ru',
            ],
            [
                'name' => 'Санкт-Петербургский государственный университет',
                'description' => 'Один из старейших университетов России',
                'address' => 'Санкт-Петербург, Университетская наб., 7/9',
                'phone' => '+7 (812) 328-20-00',
                'email' => 'info@spbu.ru',
                'website' => 'https://spbu.ru',
            ],
            [
                'name' => 'Новосибирский государственный университет',
                'description' => 'Ведущий научно-образовательный центр Сибири',
                'address' => 'Новосибирск, ул. Пирогова, 1',
                'phone' => '+7 (383) 363-40-00',
                'email' => 'info@nsu.ru',
                'website' => 'https://www.nsu.ru',
            ],
        ];

        foreach ($institutions as $institutionData) {
            Institution::create($institutionData);
        }

        // Create programs
        $programs = [
            [
                'name' => 'Информатика и вычислительная техника',
                'description' => 'Программа подготовки специалистов в области информационных технологий',
                'institution_id' => 1,
                'duration' => '4 года',
                'degree_level' => 'бакалаврский',
                'tuition_fee' => 250000,
                'language' => 'ru',
                'requirements' => ['Математика', 'Физика', 'Информатика'],
            ],
            [
                'name' => 'Прикладная математика и информатика',
                'description' => 'Программа подготовки математиков-программистов',
                'institution_id' => 2,
                'duration' => '4 года',
                'degree_level' => 'бакалаврский',
                'tuition_fee' => 280000,
                'language' => 'ru',
                'requirements' => ['Математика', 'Физика'],
            ],
            [
                'name' => 'Программная инженерия',
                'description' => 'Программа подготовки инженеров-программистов',
                'institution_id' => 3,
                'duration' => '4 года',
                'degree_level' => 'бакалаврский',
                'tuition_fee' => 300000,
                'language' => 'ru',
                'requirements' => ['Математика', 'Информатика'],
            ],
        ];

        foreach ($programs as $programData) {
            Program::create($programData);
        }

        // Create courses
        $courses = [
            [
                'name' => 'Основы программирования',
                'description' => 'Изучение основ программирования на языке Python',
                'program_id' => 1,
                'instructor_id' => 2,
                'code' => 'CS101',
                'credits' => 6,
                'duration' => '1 семестр',
                'schedule' => 'Пн, Ср, Пт 10:00-11:30',
                'location' => 'Аудитория 101',
                'prerequisites' => ['Математика'],
                'learning_outcomes' => ['Знание основ Python', 'Умение решать алгоритмические задачи'],
            ],
            [
                'name' => 'Базы данных',
                'description' => 'Изучение проектирования и работы с базами данных',
                'program_id' => 1,
                'instructor_id' => 2,
                'code' => 'CS201',
                'credits' => 4,
                'duration' => '1 семестр',
                'schedule' => 'Вт, Чт 14:00-15:30',
                'location' => 'Аудитория 201',
                'prerequisites' => ['Основы программирования'],
                'learning_outcomes' => ['Проектирование БД', 'SQL запросы'],
            ],
            [
                'name' => 'Математический анализ',
                'description' => 'Изучение основ математического анализа',
                'program_id' => 2,
                'instructor_id' => 2,
                'code' => 'MATH101',
                'credits' => 8,
                'duration' => '2 семестра',
                'schedule' => 'Пн, Ср, Пт 9:00-10:30',
                'location' => 'Аудитория 301',
                'prerequisites' => ['Школьная математика'],
                'learning_outcomes' => ['Пределы', 'Производные', 'Интегралы'],
            ],
        ];

        foreach ($courses as $courseData) {
            Course::create($courseData);
        }
    }
}
