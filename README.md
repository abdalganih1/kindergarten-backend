<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).



بالتأكيد، إليك توثيق لواجهة برمجة التطبيقات (API) التي تم تصميمها بناءً على ملف `routes/api.php` والمتحكمات التي ناقشناها، مع أمثلة عملية.

**ملاحظة:** يفترض هذا التوثيق أن Base URL لواجهة الـ API هو `http://your-domain.test/api` (استبدله بالرابط الفعلي).

---

## توثيق API نظام إدارة رياض الأطفال (لأولياء الأمور)

**الإصدار:** 1.0

**Base URL:** `http://your-domain.test/api`

### مقدمة

توفر واجهة برمجة التطبيقات هذه (API) نقاط نهاية (endpoints) لتطبيق Flutter الخاص بأولياء الأمور للتفاعل مع نظام إدارة رياض الأطفال. يتم استخدام مصادقة Sanctum المستندة إلى التوكن، ويتم إرجاع جميع الردود بتنسيق JSON.

يجب على جميع طلبات API إرسال الهيدر `Accept: application/json`.

### المصادقة (Authentication)

تستخدم الـ API نظام Laravel Sanctum للمصادقة المستندة إلى التوكن.

**1. تسجيل الدخول**

للحصول على توكن المصادقة، قم بإرسال طلب POST إلى نقطة النهاية `/login` مع بيانات اعتماد المستخدم.

*   **Endpoint:** `POST /login`
*   **الوصف:** تسجيل دخول مستخدم (ولي أمر، مشرف، مدير) وإرجاع توكن للوصول إلى الـ API.
*   **المصادقة:** لا يوجد (عامة).
*   **Body Parameters (JSON):**
    *   `email` (string, required): البريد الإلكتروني للمستخدم.
    *   `password` (string, required): كلمة المرور للمستخدم.
    *   `device_name` (string, required): اسم مميز للجهاز الذي يتم تسجيل الدخول منه (مثل 'My Flutter App').
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST http://your-domain.test/api/login \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "email": "parent1@example.com",
            "password": "password",
            "device_name": "FlutterApp_Parent1"
          }'
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "token": "1|lFsd9a7sd6fg8h7sdfg98h...", // Sanctum API Token
        "user": { // UserResource
            "id": 2,
            "name": "ولي أمر ١",
            "email": "parent1@example.com",
            "role": "Parent",
            "is_active": true,
            "created_at": "2024-03-12 10:00:00",
            "updated_at": "2024-03-12 10:00:00",
            "admin_profile": null,
            "parent_profile": { // ParentResource
                "parent_id": 1,
                "user_id": 2,
                "full_name": "ولي أمر ١",
                "contact_email": "parent1@example.com",
                "contact_phone": "987-654-3210",
                "address": "123 Main St, Anytown",
                "created_at": "2024-03-12 10:05:00"
            }
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: إذا كانت بيانات الإدخال غير صالحة (مثل حقل مفقود أو تنسيق بريد خاطئ). سيحتوي الرد على مصفوفة `errors`.
    *   `422 Unprocessable Entity` (مع رسالة في `errors.email`): إذا كانت بيانات الاعتماد غير صحيحة (`auth.failed`).
    *   `403 Forbidden`: إذا كان الحساب غير نشط أو دوره غير مسموح له بالوصول عبر API.

**2. استخدام التوكن**

بعد الحصول على التوكن، يجب إرساله مع **كل** طلب يتطلب مصادقة في هيدر `Authorization`:

```
Authorization: Bearer <your-api-token>
```

**3. الحصول على بيانات المستخدم المصادق عليه**

يمكن استخدام هذا المسار للتحقق من صحة التوكن والحصول على بيانات المستخدم الحالية.

*   **Endpoint:** `GET /user`
*   **الوصف:** إرجاع بيانات المستخدم المصادق عليه حاليًا.
*   **المصادقة:** مطلوبة (Sanctum Token).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET http://your-domain.test/api/user \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (مشابه لبيانات `user` في رد تسجيل الدخول)
    ```json
    {
        // ... UserResource data including parentProfile or adminProfile ...
    }
    ```
*   **أخطاء محتملة:**
    *   `401 Unauthorized`: إذا لم يتم إرسال التوكن أو كان غير صالح.

**4. تسجيل الخروج**

لإبطال التوكن المستخدم حاليًا.

*   **Endpoint:** `POST /logout`
*   **الوصف:** تسجيل خروج المستخدم الحالي بإبطال التوكن المستخدم للطلب.
*   **المصادقة:** مطلوبة (Sanctum Token).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST http://your-domain.test/api/logout \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "message": "Successfully logged out"
    }
    ```
*   **أخطاء محتملة:**
    *   `401 Unauthorized`: إذا لم يتم إرسال التوكن أو كان غير صالح.

---

### إدارة الملف الشخصي (لأولياء الأمور)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الملف الشخصي**

*   **Endpoint:** `GET /profile`
*   **الوصف:** عرض بيانات المستخدم وولي الأمر المصادق عليه.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **مثال الرد الناجح (200 OK):** (مشابه لـ `GET /user` ولكن يضمن وجود `parentProfile`)
    ```json
    {
        // ... UserResource data including parentProfile ...
    }
    ```

**2. تحديث الملف الشخصي**

*   **Endpoint:** `PUT /profile`
*   **الوصف:** تحديث بيانات ملف المستخدم وولي الأمر (الاسم، معلومات الاتصال).
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Body Parameters (JSON):** (أرسل فقط الحقول التي تريد تحديثها)
    *   `name` (string, optional): اسم المستخدم (الكامل).
    *   `contact_email` (string, optional): بريد الاتصال لولي الأمر (ليس بالضرورة بريد تسجيل الدخول).
    *   `contact_phone` (string, optional): هاتف الاتصال.
    *   `address` (string, optional): العنوان.
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT http://your-domain.test/api/profile \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "contact_phone": "111-222-3333",
            "address": "456 New Address St"
          }'
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات المستخدم المحدثة)
    ```json
    {
        // ... Updated UserResource data ...
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: أخطاء التحقق من الصحة (مثل تنسيق بريد خاطئ).

**3. تحديث كلمة المرور**

*   **Endpoint:** `PUT /profile/password`
*   **الوصف:** تحديث كلمة مرور المستخدم المصادق عليه.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Body Parameters (JSON):**
    *   `current_password` (string, required): كلمة المرور الحالية للمستخدم.
    *   `password` (string, required): كلمة المرور الجديدة.
    *   `password_confirmation` (string, required): تأكيد كلمة المرور الجديدة.
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT http://your-domain.test/api/profile/password \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "current_password": "old_password",
            "password": "new_strong_password",
            "password_confirmation": "new_strong_password"
          }'
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "message": "Password updated successfully."
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: إذا كانت كلمة المرور الحالية خاطئة، أو كلمة المرور الجديدة لا تطابق التأكيد، أو ضعيفة جدًا.

---

### بيانات الأطفال (Children)

تتطلب هذه المسارات دور `Parent`.

**1. عرض أطفال ولي الأمر**

*   **Endpoint:** `GET /children`
*   **الوصف:** عرض قائمة بجميع الأطفال المرتبطين بولي الأمر المصادق عليه.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // ChildResource 1
                "child_id": 1,
                "first_name": "أحمد",
                "last_name": "علي",
                "full_name": "أحمد علي",
                "date_of_birth": "2022-05-15",
                "gender": "Male",
                "enrollment_date": "2024-01-10",
                "allergies": "حساسية الفول السوداني",
                "medical_notes": "لا يوجد",
                "photo_url": "http://your-domain.test/storage/children_photos/...", // أو null
                "created_at": "...",
                "class": { // ClassResource
                    "class_id": 1,
                    "class_name": "الأطفال الصغار (أقل من 3)",
                    // ... other class fields
                },
                "parents": [ /* ParentResource - maybe omit to avoid recursion */ ],
                "health_records": [ /* HealthRecordResource - maybe omit from list view */ ],
                "attendances": [ /* AttendanceResource - maybe omit from list view */ ]
            },
            { // ChildResource 2
              // ...
            }
        ]
    }
    ```

**2. عرض تفاصيل طفل محدد**

*   **Endpoint:** `GET /children/{child}`
*   **الوصف:** عرض التفاصيل الكاملة لطفل معين ينتمي لولي الأمر.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Path Parameters:**
    *   `child` (integer, required): معرف الطفل (`child_id`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET http://your-domain.test/api/children/1 \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": { // ChildResource with loaded relations
             "child_id": 1,
             "first_name": "أحمد",
             // ... all child fields ...
             "photo_url": "http://your-domain.test/storage/children_photos/...",
             "class": { // ClassResource
                "class_id": 1,
                "class_name": "الأطفال الصغار (أقل من 3)",
                // ...
             },
             "parents": [ // ParentResource list
                 { "parent_id": 1, "full_name": "ولي أمر ١", /* ... */ }
             ],
             "health_records": [ // HealthRecordResource list
                 { "record_id": 1, "record_type": "Vaccination", /* ... */ }
             ],
             "attendances": [ // AttendanceResource list
                 { "attendance_id": 1, "attendance_date": "2024-03-12", "status": "Present", /* ... */ }
             ]
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `403 Forbidden`: إذا كان الطفل لا ينتمي لولي الأمر الحالي.
    *   `404 Not Found`: إذا لم يتم العثور على الطفل بالمعرف المحدد.

---

### الجداول الأسبوعية (Schedules)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الجداول الأسبوعية لفصول أطفال ولي الأمر**

*   **Endpoint:** `GET /schedules`
*   **الوصف:** عرض الجداول الأسبوعية للفصول التي ينتمي إليها أطفال ولي الأمر المصادق عليه.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // WeeklyScheduleResource 1 (for class 1)
                "schedule_id": 10,
                "day_of_week": "Monday",
                "start_time": "09:00:00",
                "end_time": "09:30:00",
                "activity_description": "حلقة الصباح والترحيب",
                "class": { // ClassResource
                    "class_id": 1,
                    "class_name": "الأطفال الصغار (أقل من 3)",
                    // ...
                },
                "created_by": null, // Admin relation might not be loaded here
                "created_at": "..."
            },
            { // WeeklyScheduleResource 2 (for class 2)
                "schedule_id": 25,
                "day_of_week": "Monday",
                "start_time": "09:30:00",
                "end_time": "10:30:00",
                "activity_description": "أنشطة تعليمية (حروف وأرقام)",
                "class": { // ClassResource
                    "class_id": 2,
                    "class_name": "مرحلة ما قبل المدرسة (3-6)",
                    // ...
                },
                 // ...
            }
            // ... other schedule items for relevant classes
        ]
    }
    ```

---

### السجلات الصحية (Health Records)

تتطلب هذه المسارات دور `Parent`.

**1. عرض السجلات الصحية لأطفال ولي الأمر**

*   **Endpoint:** `GET /health-records`
*   **الوصف:** عرض السجلات الصحية لجميع الأطفال المرتبطين بولي الأمر المصادق عليه.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // HealthRecordResource 1 (for child 1)
                "record_id": 1,
                "record_type": "Vaccination",
                "record_date": "2023-05-15",
                "details": "لقاح الحصبة والنكاف والحصبة الألمانية (MMR) - الجرعة الأولى",
                "next_due_date": "2027-05-15",
                "document_path": null,
                "entered_by": { // UserResource (Admin or Parent who entered)
                    "id": 1,
                    "name": "مدير النظام",
                    "role": "Admin",
                    // ...
                },
                "entered_at": "..."
            },
            { // HealthRecordResource 2 (for child 2)
              // ...
            }
        ]
    }
    ```

---

### الوجبات اليومية (Daily Meals)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الوجبات اليومية**

*   **Endpoint:** `GET /meals`
*   **الوصف:** عرض الوجبات اليومية. تعرض افتراضيًا وجبات اليوم الحالي، ويمكن فلترتها حسب التاريخ. تعرض الوجبات العامة والوجبات المخصصة لفصول أطفال ولي الأمر.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Query Parameters:**
    *   `date` (string, optional): التاريخ المطلوب بتنسيق `YYYY-MM-DD` (مثل `2024-03-15`). إذا لم يتم توفيره، يتم استخدام تاريخ اليوم الحالي.
*   **مثال الطلب (cURL - لوجبات يوم محدد):**
    ```bash
    curl -X GET "http://your-domain.test/api/meals?date=2024-03-11" \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // DailyMealResource 1
                "meal_id": 10,
                "meal_date": "2024-03-11",
                "meal_type": "Lunch",
                "menu_description": "معكرونة بالصلصة الحمراء وسلطة خضراء",
                "class": { // ClassResource (if meal is class-specific)
                    "class_id": 2,
                    "class_name": "مرحلة ما قبل المدرسة (3-6)",
                    // ...
                },
                "created_at": "..."
            },
            { // DailyMealResource 2 (General snack)
                "meal_id": 11,
                "meal_date": "2024-03-11",
                "meal_type": "Snack",
                "menu_description": "زبادي وفواكه",
                "class": null,
                "created_at": "..."
            }
        ]
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: إذا كان تنسيق `date` غير صالح.

---

### الإعلانات (Announcements)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الإعلانات**

*   **Endpoint:** `GET /announcements`
*   **الوصف:** عرض الإعلانات العامة والإعلانات الموجهة لفصول أطفال ولي الأمر.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // AnnouncementResource 1 (General)
                "announcement_id": 1,
                "title": "أهلاً وسهلاً بالعام الدراسي الجديد",
                "content": "نرحب بجميع الأطفال وأولياء الأمور...",
                "publish_date": "...",
                "author": { // AdminResource
                    "admin_id": 1,
                    "full_name": "مدير النظام",
                    // ...
                },
                "target_class": null,
                "created_at": "..."
            },
             { // AnnouncementResource 2 (Targeted)
                "announcement_id": 2,
                "title": "تذكير برحلة الحديقة",
                "content": "نود تذكير أولياء أمور فصل (3-6)...",
                "publish_date": "...",
                "author": { /* ... */ },
                "target_class": { // ClassResource
                    "class_id": 2,
                    "class_name": "مرحلة ما قبل المدرسة (3-6)",
                     // ...
                },
                "created_at": "..."
            }
            // ... other relevant announcements
        ]
    }
    ```

---

### الوسائط (Media - Photos/Videos)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الوسائط**

*   **Endpoint:** `GET /media`
*   **الوصف:** عرض ملفات الوسائط (صور/فيديو) المرتبطة بأطفال ولي الأمر، أو فصولهم، أو فعالياتهم المسجلين بها، أو الوسائط العامة. يتم عرضها مع Pagination.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Query Parameters:**
    *   `per_page` (integer, optional): عدد العناصر لكل صفحة (الافتراضي 12).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // MediaResource 1
                "media_id": 15,
                "file_url": "http://your-domain.test/storage/uploads/images/sample_art_craft.jpg",
                "media_type": "Image",
                "description": "الأطفال يستمتعون بوقت الفنون...",
                "upload_date": "...",
                "uploader": { // UserResource
                    "id": 1,
                    "name": "مدير النظام",
                    // ...
                },
                "associated_child": null,
                "associated_event": null,
                "associated_class": { // ClassResource
                    "class_id": 2,
                    "class_name": "مرحلة ما قبل المدرسة (3-6)",
                    // ...
                 }
            },
            { // MediaResource 2 (associated with child)
                "media_id": 16,
                "file_url": "http://your-domain.test/storage/children_photos/child_photo.jpg",
                "media_type": "Image",
                "description": "صورة أحمد في الفصل",
                "upload_date": "...",
                "uploader": { /* ... */ },
                "associated_child": { // ChildResource (maybe minimal fields here)
                    "child_id": 1,
                    "full_name": "أحمد علي",
                     // ...
                 },
                "associated_event": null,
                "associated_class": null
            }
            // ... other media items
        ],
        "links": { /* ... pagination links ... */ },
        "meta": { /* ... pagination meta ... */ }
    }
    ```

---

### الفعاليات والتسجيل (Events & Registration)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الفعاليات**

*   **Endpoint:** `GET /events`
*   **الوصف:** عرض قائمة بالفعاليات القادمة أو النشطة مع Pagination.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Query Parameters:**
    *   `per_page` (integer, optional): عدد العناصر لكل صفحة (الافتراضي 10).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // EventResource 1
                "event_id": 1,
                "event_name": "رحلة إلى حديقة الحيوان",
                "description": "زيارة تعليمية وترفيهية...",
                "event_date": "2024-04-15 09:00:00",
                "location": "حديقة الحيوان المحلية",
                "requires_registration": true,
                "registration_deadline": "2024-04-01 17:00:00",
                "created_by": { // AdminResource
                    "admin_id": 1, /* ... */
                },
                "created_at": "...",
                "registrations_count": 2 // Example count
            },
            { // EventResource 2
              // ...
            }
        ],
        "links": { /* ... */ },
        "meta": { /* ... */ }
    }
    ```

**2. عرض تفاصيل فعالية محددة**

*   **Endpoint:** `GET /events/{event}`
*   **الوصف:** عرض تفاصيل فعالية محددة، مع الإشارة إلى ما إذا كان أي من أطفال ولي الأمر مسجلين بها.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Path Parameters:**
    *   `event` (integer, required): معرف الفعالية (`event_id`).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": { // EventResource
             "event_id": 1,
             "event_name": "رحلة إلى حديقة الحيوان",
             // ... other event fields ...
             "created_by": { /* ... */ },
             "registrations": [ // EventRegistrationResource list
                 {
                     "registration_id": 1,
                     "registration_date": "...",
                     "parent_consent": true,
                     "event": null, // Avoid deep nesting if not needed
                     "child": { // ChildResource (minimal)
                         "child_id": 2,
                         "full_name": "فاطمة محمد"
                     }
                 },
                 { /* ... for child 3 ... */ }
             ],
             "registrations_count": 2
        },
        "meta": {
            "is_registered_by_current_user": true // أو false
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `404 Not Found`: إذا لم يتم العثور على الفعالية.

**3. تسجيل طفل في فعالية**

*   **Endpoint:** `POST /events/{event}/register`
*   **الوصف:** تسجيل طفل محدد (ينتمي لولي الأمر) في فعالية تتطلب التسجيل.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Path Parameters:**
    *   `event` (integer, required): معرف الفعالية (`event_id`).
*   **Body Parameters (JSON):**
    *   `child_id` (integer, required): معرف الطفل المراد تسجيله.
    *   `parent_consent` (boolean, optional): موافقة ولي الأمر (إذا كان الحقل مطلوبًا).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST http://your-domain.test/api/events/1/register \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "child_id": 1,
            "parent_consent": true
          }'
    ```
*   **مثال الرد الناجح (201 Created):**
    ```json
    {
        "data": { // EventRegistrationResource
            "registration_id": 3,
            "registration_date": "...",
            "parent_consent": true,
            "event": { // EventResource (minimal)
                "event_id": 1,
                "event_name": "رحلة إلى حديقة الحيوان"
            },
            "child": { // ChildResource (minimal)
                "child_id": 1,
                "full_name": "أحمد علي"
            }
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `404 Not Found`: الفعالية غير موجودة.
    *   `422 Unprocessable Entity`: خطأ في التحقق (الطفل لا ينتمي لولي الأمر، حقول مفقودة).
    *   `400 Bad Request`: الفعالية لا تتطلب تسجيل أو الموعد النهائي انتهى.
    *   `409 Conflict`: الطفل مسجل بالفعل.

**4. إلغاء تسجيل طفل من فعالية**

*   **Endpoint:** `DELETE /event-registrations/{registration}`
*   **الوصف:** إلغاء تسجيل طفل من فعالية بناءً على معرف التسجيل.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Path Parameters:**
    *   `registration` (integer, required): معرف التسجيل (`registration_id`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X DELETE http://your-domain.test/api/event-registrations/3 \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "message": "Registration cancelled successfully."
    }
    ```
*   **أخطاء محتملة:**
    *   `404 Not Found`: التسجيل غير موجود.
    *   `403 Forbidden`: التسجيل لا يخص طفل ولي الأمر الحالي.
    *   `400 Bad Request`: (اختياري) لا يمكن الإلغاء بعد بدء الفعالية.

---

### المصادر التعليمية (Educational Resources)

تتطلب هذه المسارات دور `Parent`.

**1. عرض المصادر التعليمية**

*   **Endpoint:** `GET /educational-resources`
*   **الوصف:** عرض قائمة بالمصادر التعليمية مع Pagination.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Query Parameters:**
    *   `per_page` (integer, optional): عدد العناصر لكل صفحة (الافتراضي 15).
    *   `subject` (string, optional): فلترة حسب الموضوع.
    *   `age` (integer, optional): فلترة حسب عمر الطفل لعرض المصادر المناسبة.
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // EducationalResource 1
                "resource_id": 1,
                "title": "أغنية الحروف الأبجدية",
                "description": "فيديو تعليمي ممتع...",
                "resource_type": "Video",
                "url_or_path": "https://youtube.com/...",
                "target_age_min": 3,
                "target_age_max": 6,
                "subject": "اللغة العربية",
                "added_by": { // AdminResource
                    "admin_id": 1, /* ... */
                 },
                "added_at": "..."
            },
            { // EducationalResource 2
              // ...
            }
        ],
        "links": { /* ... */ },
        "meta": { /* ... */ }
    }
    ```

---

### الملاحظات (Observations)

تتطلب هذه المسارات دور `Parent`.

**1. إرسال ملاحظة/تعليق**

*   **Endpoint:** `POST /observations`
*   **الوصف:** إرسال ملاحظة جديدة من ولي الأمر (قد تكون عامة أو مرتبطة بطفل معين).
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Body Parameters (JSON):**
    *   `observation_text` (string, required): نص الملاحظة.
    *   `child_id` (integer, optional): معرف الطفل الذي تتعلق به الملاحظة (إذا كانت مرتبطة بطفل).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST http://your-domain.test/api/observations \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "observation_text": "لاحظت أن طفلي يستمتع بأنشطة الفن.",
            "child_id": 1
          }'
    ```
*   **مثال الرد الناجح (201 Created):**
    ```json
    {
        "data": { // ObservationResource
            "observation_id": 5,
            "observation_text": "لاحظت أن طفلي يستمتع بأنشطة الفن.",
            "submitted_at": "...",
            "parent_submitter": { // ParentResource
                "parent_id": 1,
                "full_name": "ولي أمر ١",
                // ...
            },
            "child": { // ChildResource (minimal)
                "child_id": 1,
                "full_name": "أحمد علي"
             }
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: أخطاء التحقق (نص مفقود، `child_id` غير صحيح أو لا ينتمي لولي الأمر).

---

### الرسائل (Messages)

تتطلب هذه المسارات دور `Parent`.

**1. عرض الرسائل**

*   **Endpoint:** `GET /messages`
*   **الوصف:** عرض قائمة بأحدث الرسائل المرسلة أو المستلمة للمستخدم الحالي مع Pagination.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Query Parameters:**
    *   `per_page` (integer, optional): عدد العناصر لكل صفحة (الافتراضي 20).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // MessageResource 1
                "message_id": 10,
                "subject": "بخصوص رحلة الحديقة",
                "body": "السيدة/السيد ولي أمر ١، يرجى التأكد...",
                "sent_at": "...",
                "read_at": null, // أو تاريخ القراءة
                "sender": { // UserResource (Admin)
                    "id": 1,
                    "name": "مدير النظام",
                    "role": "Admin",
                    // ...
                 },
                "recipient": { // UserResource (Parent)
                    "id": 2,
                    "name": "ولي أمر ١",
                    "role": "Parent",
                     // ...
                 }
            },
            { // MessageResource 2 (Sent by Parent)
                "message_id": 11,
                "subject": "استفسار عن الواجب",
                "body": "هل يوجد واجب منزلي اليوم؟",
                "sent_at": "...",
                "read_at": "...",
                "sender": { // UserResource (Parent)
                    "id": 2, /* ... */
                },
                "recipient": { // UserResource (Supervisor)
                    "id": 4,
                    "name": "مشرف ١",
                    "role": "Supervisor",
                     /* ... */
                 }
            }
        ],
         "links": { /* ... */ },
        "meta": { /* ... */ }
    }
    ```

**2. إرسال رسالة جديدة**

*   **Endpoint:** `POST /messages`
*   **الوصف:** إرسال رسالة جديدة من ولي الأمر إلى مدير أو مشرف.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Body Parameters (JSON):**
    *   `recipient_id` (integer, required): معرف المستخدم المستلم (يجب أن يكون Admin أو Supervisor).
    *   `subject` (string, optional): موضوع الرسالة.
    *   `body` (string, required): نص الرسالة.
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST http://your-domain.test/api/messages \
      -H "Authorization: Bearer <your-api-token>" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "recipient_id": 1,
            "subject": "استفسار بخصوص الرسوم",
            "body": "متى يتم دفع رسوم الشهر القادم؟ شكراً."
          }'
    ```
*   **مثال الرد الناجح (201 Created):**
    ```json
    {
        "data": { // MessageResource
            "message_id": 12,
            "subject": "استفسار بخصوص الرسوم",
            "body": "متى يتم دفع رسوم الشهر القادم؟ شكراً.",
            "sent_at": "...",
            "read_at": null,
            "sender": { // UserResource (Parent)
                "id": 2, /* ... */
            },
            "recipient": { // UserResource (Admin)
                "id": 1, /* ... */
             }
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `422 Unprocessable Entity`: أخطاء التحقق (مستلم غير موجود، مستلم ليس مدير/مشرف، حقول مفقودة، إرسال لنفس المستخدم).

**3. عرض رسالة محددة**

*   **Endpoint:** `GET /messages/{message}`
*   **الوصف:** عرض تفاصيل رسالة محددة (إذا كان المستخدم هو المرسل أو المستقبل). سيتم تحديد الرسالة كمقروءة إذا كان المستخدم الحالي هو المستقبل ولم تُقرأ بعد.
*   **المصادقة:** مطلوبة (Sanctum Token, Role: Parent).
*   **Path Parameters:**
    *   `message` (integer, required): معرف الرسالة (`message_id`).
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": { // MessageResource
            "message_id": 10,
            "subject": "بخصوص رحلة الحديقة",
             // ... other fields like above ...
             "read_at": "2024-03-13 09:00:00" // تم تحديثها الآن
        }
    }
    ```
*   **أخطاء محتملة:**
    *   `404 Not Found`: الرسالة غير موجودة.
    *   `403 Forbidden`: المستخدم ليس المرسل أو المستقبل.

---

### الأخطاء الشائعة

*   **401 Unauthorized:** التوكن مفقود، غير صالح، أو منتهي الصلاحية. يجب على المستخدم تسجيل الدخول مرة أخرى.
*   **403 Forbidden:** المستخدم مسجل دخوله ولكن ليس لديه الصلاحية للوصول إلى هذا المسار أو المورد (إما بسبب الدور أو ملكية المورد).
*   **404 Not Found:** المورد المطلوب (مثل طفل، فعالية، رسالة) غير موجود بالمعرف المحدد.
*   **422 Unprocessable Entity:** خطأ في التحقق من صحة البيانات المرسلة في الطلب. سيحتوي الرد على مصفوفة `errors` توضح الحقول التي بها أخطاء.
    ```json
    {
        "message": "The given data was invalid.",
        "errors": {
            "field_name": [
                "Error message 1 for this field.",
                "Error message 2 for this field."
            ],
            "another_field": [ /* ... */ ]
        }
    }
    ```
*   **409 Conflict:** محاولة إنشاء مورد موجود بالفعل (مثل تسجيل طفل مسجل مسبقًا في فعالية).
*   **500 Internal Server Error:** خطأ عام في الخادم.

-------------------


بالتأكيد، إليك قائمة تفصيلية بنقاط نهاية الـ API بناءً على ملف `routes/api.php` الذي قدمته:

---

## قائمة نقاط نهاية API نظام إدارة رياض الأطفال

**Base URL:** (افترض أنه `http://your-domain.test/api`)

---

### قسم المصادقة والملف الشخصي (Authentication & Profile)

**1. تسجيل الدخول**

*   **Method:** `POST`
*   **URI:** `/login`
*   **Controller@Method:** `Api\AuthController@login`
*   **Middleware:** `api` (عام، لا يتطلب مصادقة مسبقة)
*   **Parameters:**
    *   **Body (JSON):** `email` (string, required), `password` (string, required), `device_name` (string, required)
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "token": "SANCTUM_API_TOKEN",
      "user": { /* UserResource data (including parentProfile/adminProfile if loaded) */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `422 Unprocessable Entity`: بيانات غير صالحة (مفقودة، تنسيق خاطئ، بيانات اعتماد خاطئة `auth.failed`).
    *   `403 Forbidden`: حساب غير نشط أو دور غير مسموح به.

**2. تسجيل حساب جديد (ولي أمر)**

*   **Method:** `POST`
*   **URI:** `/register`
*   **Controller@Method:** `Api\RegisterController@register` (إذا تم تفعيل المسار وإنشاء المتحكم)
*   **Middleware:** `api` (عام)
*   **Parameters:**
    *   **Body (JSON):** `name` (string, required), `email` (string, required, unique), `password` (string, required, confirmed), `password_confirmation` (string, required), `contact_phone` (string, optional), `address` (string, optional), `device_name` (string, required).
*   **الاستجابة المتوقعة (Success - 201 Created):**
    ```json
    {
      "token": "SANCTUM_API_TOKEN",
      "user": { /* UserResource data (including parentProfile) */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `422 Unprocessable Entity`: أخطاء التحقق من الصحة (بريد مستخدم، كلمة مرور ضعيفة، إلخ).

**3. تسجيل الخروج**

*   **Method:** `POST`
*   **URI:** `/logout`
*   **Controller@Method:** `Api\AuthController@logout`
*   **Middleware:** `auth:sanctum`
*   **Parameters:** لا يوجد (يعتمد على التوكن المرسل).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "message": "Successfully logged out" }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `401 Unauthorized`: التوكن غير صالح أو مفقود.

**4. الحصول على بيانات المستخدم الحالي**

*   **Method:** `GET`
*   **URI:** `/user`
*   **Controller@Method:** Closure (دالة مضمنة)
*   **Middleware:** `auth:sanctum`
*   **Parameters:** لا يوجد.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { /* UserResource data (loads parentProfile specifically for now) */ }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `401 Unauthorized`: التوكن غير صالح أو مفقود.

**5. عرض ملف ولي الأمر الشخصي**

*   **Method:** `GET`
*   **URI:** `/profile`
*   **Controller@Method:** `Api\ProfileController@show`
*   **Middleware:** `auth:sanctum`, `role:Parent`
*   **Parameters:** لا يوجد.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { /* UserResource data including parentProfile */ }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `401 Unauthorized`: التوكن غير صالح أو مفقود.
    *   `403 Forbidden`: المستخدم ليس ولي أمر أو ملفه الشخصي غير موجود.

**6. تحديث ملف ولي الأمر الشخصي**

*   **Method:** `PUT`
*   **URI:** `/profile`
*   **Controller@Method:** `Api\ProfileController@update`
*   **Middleware:** `auth:sanctum`, `role:Parent`
*   **Parameters:**
    *   **Body (JSON):** `name` (string, optional), `contact_email` (string, optional, email, unique:parents), `contact_phone` (string, optional), `address` (string, optional).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { /* Updated UserResource data including parentProfile */ }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `401 Unauthorized`.
    *   `403 Forbidden`.
    *   `422 Unprocessable Entity`: أخطاء التحقق.

**7. تحديث كلمة مرور ولي الأمر**

*   **Method:** `PUT`
*   **URI:** `/profile/password`
*   **Controller@Method:** `Api\ProfileController@updatePassword`
*   **Middleware:** `auth:sanctum`, `role:Parent`
*   **Parameters:**
    *   **Body (JSON):** `current_password` (string, required), `password` (string, required, confirmed), `password_confirmation` (string, required).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "message": "Password updated successfully." }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):**
    *   `401 Unauthorized`.
    *   `403 Forbidden`.
    *   `422 Unprocessable Entity`: أخطاء التحقق (كلمة المرور الحالية خاطئة، التأكيد غير مطابق، كلمة المرور الجديدة ضعيفة).

---

### قسم أولياء الأمور (Parent Specific Routes)

**جميع هذه المسارات تتطلب `auth:sanctum` و `role:Parent` Middleware.**

**8. عرض أطفال ولي الأمر**

*   **Method:** `GET`
*   **URI:** `/children`
*   **Controller@Method:** `Api\ChildController@index`
*   **Parameters:** لا يوجد Query parameters حاليًا (يمكن إضافة فلترة/ترتيب لاحقًا).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": [ /* Array of ChildResource objects */ ] }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**9. عرض تفاصيل طفل محدد**

*   **Method:** `GET`
*   **URI:** `/children/{child}`
*   **Controller@Method:** `Api\ChildController@show`
*   **Parameters:**
    *   **Path:** `child` (integer, required): ID الطفل.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": { /* ChildResource data with loaded relations */ } }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403 (إذا كان الطفل لا ينتمي لولي الأمر), 404 (إذا لم يوجد الطفل).

**10. عرض الجداول الأسبوعية**

*   **Method:** `GET`
*   **URI:** `/schedules`
*   **Controller@Method:** `Api\ScheduleController@index`
*   **Parameters:** لا يوجد Query parameters حاليًا.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": [ /* Array of WeeklyScheduleResource objects for parent's children's classes */ ] }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**11. عرض السجلات الصحية**

*   **Method:** `GET`
*   **URI:** `/health-records`
*   **Controller@Method:** `Api\HealthRecordController@index`
*   **Parameters:** لا يوجد Query parameters حاليًا.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": [ /* Array of HealthRecordResource objects for parent's children */ ] }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**12. عرض الوجبات اليومية**

*   **Method:** `GET`
*   **URI:** `/meals`
*   **Controller@Method:** `Api\DailyMealController@index`
*   **Parameters:**
    *   **Query:** `date` (string, optional, `YYYY-MM-DD`).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": [ /* Array of DailyMealResource objects for the specified date, relevant to parent */ ] }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 422 (إذا كان تنسيق التاريخ خاطئ).

**13. عرض حالات وجبات الأطفال**

*   **Method:** `GET`
*   **URI:** `/meal-statuses`
*   **Controller@Method:** `Api\ChildMealStatusController@index`
*   **Parameters:**
    *   **Query:** `date` (string, optional, `YYYY-MM-DD`), `child_id` (integer, optional), `per_page` (integer, optional).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": [ /* Array of ChildMealStatusResource objects */ ],
      "links": { /* Pagination links */ },
      "meta": { /* Pagination meta */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 422 (تنسيق تاريخ خاطئ، `child_id` غير صالح أو لا ينتمي لولي الأمر).

**14. عرض الإعلانات**

*   **Method:** `GET`
*   **URI:** `/announcements`
*   **Controller@Method:** `Api\AnnouncementController@index`
*   **Parameters:** لا يوجد Query parameters حاليًا.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": [ /* Array of AnnouncementResource objects relevant to parent */ ] }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**15. عرض الوسائط**

*   **Method:** `GET`
*   **URI:** `/media`
*   **Controller@Method:** `Api\MediaController@index`
*   **Parameters:**
    *   **Query:** `per_page` (integer, optional). (يمكن إضافة فلاتر أخرى لاحقًا).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": [ /* Array of MediaResource objects relevant to parent */ ],
      "links": { /* Pagination links */ },
      "meta": { /* Pagination meta */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**16. عرض الفعاليات**

*   **Method:** `GET`
*   **URI:** `/events`
*   **Controller@Method:** `Api\EventController@index`
*   **Parameters:**
    *   **Query:** `per_page` (integer, optional).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": [ /* Array of EventResource objects */ ],
      "links": { /* Pagination links */ },
      "meta": { /* Pagination meta */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**17. عرض تفاصيل فعالية محددة**

*   **Method:** `GET`
*   **URI:** `/events/{event}`
*   **Controller@Method:** `Api\EventController@show`
*   **Parameters:**
    *   **Path:** `event` (integer, required): ID الفعالية.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": { /* EventResource data with registrations */ },
      "meta": { "is_registered_by_current_user": true|false }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 404.

**18. تسجيل طفل في فعالية**

*   **Method:** `POST`
*   **URI:** `/events/{event}/register`
*   **Controller@Method:** `Api\EventRegistrationController@store`
*   **Parameters:**
    *   **Path:** `event` (integer, required): ID الفعالية.
    *   **Body (JSON):** `child_id` (integer, required), `parent_consent` (boolean, optional).
*   **الاستجابة المتوقعة (Success - 201 Created):**
    ```json
    { "data": { /* EventRegistrationResource data */ } }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 404 (event), 422 (validation: child_id invalid/unauthorized), 400 (event doesn't require registration / deadline passed), 409 (already registered).

**19. إلغاء تسجيل طفل من فعالية**

*   **Method:** `DELETE`
*   **URI:** `/event-registrations/{registration}`
*   **Controller@Method:** `Api\EventRegistrationController@destroy`
*   **Parameters:**
    *   **Path:** `registration` (integer, required): ID التسجيل.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "message": "Registration cancelled successfully." }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403 (التسجيل لا يخص أطفال ولي الأمر), 404 (التسجيل غير موجود).

**20. عرض المصادر التعليمية**

*   **Method:** `GET`
*   **URI:** `/educational-resources`
*   **Controller@Method:** `Api\EducationalResourceController@index`
*   **Parameters:**
    *   **Query:** `per_page` (integer, optional). (يمكن إضافة فلاتر أخرى).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": [ /* Array of EducationalResource objects */ ],
      "links": { /* Pagination links */ },
      "meta": { /* Pagination meta */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**21. إرسال ملاحظة**

*   **Method:** `POST`
*   **URI:** `/observations`
*   **Controller@Method:** `Api\ObservationController@store`
*   **Parameters:**
    *   **Body (JSON):** `observation_text` (string, required), `child_id` (integer, optional).
*   **الاستجابة المتوقعة (Success - 201 Created):**
    ```json
    { "data": { /* ObservationResource data */ } }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 422 (validation: text missing, invalid child_id).

**22. عرض الرسائل**

*   **Method:** `GET`
*   **URI:** `/messages`
*   **Controller@Method:** `Api\MessageController@index`
*   **Parameters:**
    *   **Query:** `per_page` (integer, optional).
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    {
      "data": [ /* Array of MessageResource objects for the user */ ],
      "links": { /* Pagination links */ },
      "meta": { /* Pagination meta */ }
    }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403.

**23. إرسال رسالة**

*   **Method:** `POST`
*   **URI:** `/messages`
*   **Controller@Method:** `Api\MessageController@store`
*   **Parameters:**
    *   **Body (JSON):** `recipient_id` (integer, required, must be Admin/Supervisor), `subject` (string, optional), `body` (string, required).
*   **الاستجابة المتوقعة (Success - 201 Created):**
    ```json
    { "data": { /* MessageResource data */ } }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403, 422 (validation: invalid recipient, body missing, sending to self).

**24. عرض رسالة محددة**

*   **Method:** `GET`
*   **URI:** `/messages/{message}`
*   **Controller@Method:** `Api\MessageController@show`
*   **Parameters:**
    *   **Path:** `message` (integer, required): ID الرسالة.
*   **الاستجابة المتوقعة (Success - 200 OK):**
    ```json
    { "data": { /* MessageResource data (marked as read if recipient) */ } }
    ```
*   **الاستجابات الخاطئة المتوقعة (Errors):** 401, 403 (ليست رسالة المستخدم), 404.

---

هذه القائمة تغطي جميع نقاط النهاية المحددة في ملف `routes/api.php` وتوفر أساسًا قويًا لتوثيق واختبار الـ API الخاص بك. تذكر أن التفاصيل الدقيقة للبيانات المرتجعة تعتمد على تصميم الـ API Resources المقابلة.

