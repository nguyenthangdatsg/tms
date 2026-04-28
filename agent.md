# TMS (Training Management System)

## Overview

TMS là hệ thống quản lý đào tạo được xây dựng trên Laravel/Livewire, tích hợp với Moodle database.

**URL**: https://lms.minhtrungai.com/tms/

## Tech Stack

- **Backend**: Laravel 13 + Livewire 4
- **Database**: Moodle MySQL database (mdl_user, mdl_course, etc.)
- **Frontend**: Bootstrap 5 + Blade templates
- **PHP**: 8.3

## Directory Structure

```
/var/www/moodle/tms/
├── app/
│   ├── Helpers/
│   │   ├── LangHelper.php      # Translation helper class
│   │   └── global.php         # Global __t() function
│   ├── Http/
│   │   └── Middleware/
│   │       └── InitLang.php   # Language initialization middleware
│   ├── Livewire/
│   │   ├── Home.php           # Dashboard
│   │   ├── Exam.php          # Exam management
│   │   ├── OnlineCourse.php  # Online courses
│   │   ├── OfflineCourse.php # Offline courses
│   │   ├── BlendedCourse.php # Blended courses (hỗn hợp)
│   │   ├── UserManagement.php # User CRUD
│   │   └── LanguageSwitcher.php # Language switcher
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── HelperServiceProvider.php
│       └── MoodleServiceProvider.php
├── lang/
│   ├── vi/messages.php        # Vietnamese translations
│   └── en/messages.php        # English translations
├── resources/views/
│   ├── layouts/app.blade.php  # Main layout
│   └── livewire/              # Livewire components
└── routes/web.php             # Routes
```

## Routes

| Route | Component | Description |
|-------|-----------|-------------|
| `/tms` | Home | Dashboard |
| `/tms/exam` | Exam | Quản lý kỳ thi |
| `/tms/online-course` | OnlineCourse | Khóa học online |
| `/tms/offline-course` | OfflineCourse | Khóa học offline |
| `/tms/blended-course` | BlendedCourse | Khóa học blended (hỗn hợp) |
| `/tms/course/{id}` | CourseDetail | Chi tiết & chỉnh sửa khóa học |
| `/tms/users` | UserManagement | Quản lý user |

## System Workflow

### 1. Admin Phân Quyền
- Admin gán permissions cho users trong TMS (`PermissionManagement`)
- Permissions xác định user có quyền thao tác gì trong hệ thống

### 2. Catalogue Quản Lý Khóa Học Gốc
- Users tạo **Catalogue Courses** (khóa học gốc/trung tâm)
- Catalogue là nơi quản lý danh sách khóa học chính của tổ chức

### 3. Tạo Khóa Học Con (Online/Offline/Blended)
- Từ Catalogue, tạo các khóa học con với **mã liên kết** (catalogue_code)
- 3 loại khóa học:
  - **Online**: Khóa học online công khai
  - **Offline**: Khóa học offline nội bộ
  - **Blended**: Khóa học hỗn hợp (Online + Offline)
- Mã catalogue dùng để map với khóa học gốc trong Catalogue

### 4. Activity Trong Khóa Học
- Sử dụng **Moodle Activities** (quiz, scorm, resource, etc.)
- Activities được quản lý trong từng khóa học

### 5. Kỳ Thi Với Bộ Đề TMS
- Kỳ thi tạo từ **Question Sets** (bộ đề trong TMS)
- Question Sets chứa các câu hỏi được quản lý riêng trong TMS
- Quiz kỳ thi được tạo trong Moodle nhưng sử dụng bộ đề từ TMS

## Features

### User Management
- **Create**: Thêm user mới vào mdl_user
- **Read**: Liệt kê users với pagination (10/20/50/100 per page)
- **Update**: Chỉnh sửa thông tin user
- **Delete**: Soft delete (set `deleted = 1` trong mdl_user)
- **Search**: Tìm kiếm theo tên, email, username

### Language Support
- **Vietnamese (vi)**: Ngôn ngữ mặc định
- **English (en)**: Ngôn ngữ thứ hai
- **Switching**: Sử dụng query parameter `?lang=vi` hoặc `?lang=en`

### Course Types (Custom Fields)
Moodle Custom Field `coursetype` được sử dụng để phân loại khóa học:
- **Online**: Khóa học online công khai (visible = 1)
- **Offline**: Khóa học offline nội bộ (visible = 0)  
- **Blended**: Khóa học hỗn hợp (Online + Offline)

Các giá trị được lưu trong `mdl_customfield_field` (id: 8) và `mdl_customfield_data`.

## Translation System

### Helper Function
```php
__t('key'); // Returns translated string
```

### Translation Files
- `/tms/lang/vi/messages.php` - Tiếng Việt
- `/tms/lang/en/messages.php` - English

### How It Works
1. `LangHelper.php` - Class xử lý translation, load file PHP
2. `global.php` - Định nghĩa hàm `__t()` ở global scope
3. `composer.json` autoload files - Load helper trước khi Laravel boot
4. `InitLang` middleware - Xử lý `?lang=` query param và set session

## Database (Moodle)

### User Table (mdl_user)
| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| username | varchar | Tên đăng nhập |
| firstname | varchar | Tên |
| lastname | varchar | Họ |
| email | varchar | Email |
| deleted | tinyint | 0 = active, 1 = deleted |
| timecreated | int | Timestamp created |

### Course Custom Field (mdl_customfield_field)
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| shortname | varchar | Unique identifier (e.g., 'coursetype') |
| name | varchar | Display name (e.g., 'Course Type') |
| type | varchar | Field type ('select', 'text', 'checkbox', etc.) |
| categoryid | bigint | Category ID |
| configdata | longtext | JSON config (options, visibility, etc.) |

### Course Custom Field Data (mdl_customfield_data)
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| fieldid | bigint | Links to mdl_customfield_field.id |
| instanceid | bigint | Course ID (mdl_course.id) |
| value | longtext | The value (for select: 'Online' or 'Offline') |

### Course Type Custom Field
Đã tạo custom field "coursetype" để phân biệt khóa học Online/Offline:
- **shortname**: `coursetype`
- **name**: `Course Type`
- **type**: `select`
- **options**: `Online\nOffline`
- **default**: `Online`

### Query Examples
```php
// Get all active users
SELECT * FROM mdl_user WHERE deleted = 0 AND username != 'guest';

// Soft delete user
UPDATE mdl_user SET deleted = 1 WHERE id = ?;

// Get course type for a specific course
SELECT d.value, f.name 
FROM mdl_customfield_data d
JOIN mdl_customfield_field f ON d.fieldid = f.id
WHERE d.instanceid = ? AND f.shortname = 'coursetype';

// Get all Online courses
SELECT c.* 
FROM mdl_course c
JOIN mdl_customfield_data d ON c.id = d.instanceid
JOIN mdl_customfield_field f ON d.fieldid = f.id
WHERE f.shortname = 'coursetype' AND d.value = 'Online';
```

## Nginx Configuration

Location: `/etc/nginx/sites-available/lms.minhtrungai.com.conf`

Key config for TMS:
- `/tms/` - Serve Laravel app
- `/tms/[\w\-]+/livewire.js` - Livewire JS assets
- PHP-FPM: unix:/var/run/php/php8.3-fpm.sock

## Commands

```bash
# Clear view cache
php artisan view:clear

# Clear config cache
php artisan config:clear

# Rebuild autoload
composer dump-autoload

# Run migrations
php artisan migrate
```

## Testing

### Run Tests
```bash
cd /var/www/moodle/tms
php artisan test tests/Unit
```

### Test Coverage
Hiện tại có **71 unit tests** covering:

| Module | Test File | Coverage |
|--------|-----------|----------|
| LangHelper | `LangHelperTest.php` | Translation, locale |
| User Management | `UserFilteringTest.php` | Search, pagination |
| Course (Online/Offline/Blended) | `CourseFilteringTest.php` | Search, pagination |
| Quiz/Exam | `QuizExamLogicTest.php` | Search, status, pagination |
| Question Bank | `QuestionBankLogicTest.php` | Search, filter |
| Question Set | `QuestionSetLogicTest.php` | Search, filter |
| Enrolled Users | `EnrolledUserLogicTest.php` | Search, enrol method filter |
| Excel Import | `ExcelImportLogicTest.php` | Row parsing, validation |

### Test Naming Convention
- Unit tests: `tests/Unit/*LogicTest.php`
- Feature tests: `tests/Feature/*.php`

### Writing New Tests
**Quy tắc:** Khi tạo chức năng mới, viết automation test song song

```php
// tests/Unit/NewFeatureLogicTest.php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NewFeatureLogicTest extends TestCase
{
    private function filterItems(array $items, string $search): array
    {
        if (empty($search)) {
            return $items;
        }
        $search = strtolower($search);
        return array_filter($items, function($item) use ($search) {
            return strpos(strtolower($item['name'] ?? ''), $search) !== false;
        });
    }

    public function test_filter_finds_by_name(): void
    {
        $items = [
            ['name' => 'Item A'],
            ['name' => 'Item B'],
        ];
        $result = $this->filterItems($items, 'a');
        $this->assertCount(1, $result);
    }
}
```

### Test Philosophy
- Test **business logic** (filtering, pagination, validation)
- Mock database calls, test pure functions
- Aim for **>80% coverage** on logic

## Development Notes

1. **Session handling**: Sử dụng raw `$_SESSION` thay vì Laravel session helper vì helper được load trước khi Laravel boot

2. **Translation loading**: File translation được load lazy trong `LangHelper::init()`, không load ngay lập tức

3. **Soft delete**: Không xóa vĩnh viễn user mà set `deleted = 1`

4. **Pagination**: Tính toán thủ công trong Livewire component, không dùng Laravel pagination

## Known Issues

- LSP errors trong VS Code do helper load trước Laravel boot (không ảnh hưởng runtime)
- Livewire POST requests có thể cần cấu hình thêm cho CSRF
