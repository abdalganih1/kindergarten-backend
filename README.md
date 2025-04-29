# نظام إدارة رياض الأطفال - الواجهة الخلفية (Backend)

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

هذا المستودع يحتوي على الواجهة الخلفية (Backend) لتطبيق نظام إدارة رياض الأطفال، المبني باستخدام إطار العمل Laravel. يوفر هذا الـ Backend واجهة برمجة تطبيقات (API) لتطبيق الواجهة الأمامية (Flutter) المستخدم من قبل أولياء الأمور، بالإضافة إلى واجهة ويب للإدارة (المدير والمشرف).

## الميزات الرئيسية

*   إدارة حسابات المستخدمين (مدير، مشرف، ولي أمر).
*   إدارة بيانات الأطفال وملفاتهم الشخصية وربطهم بأولياء الأمور.
*   إدارة الفصول الدراسية والمراحل العمرية.
*   إدارة الجداول الأسبوعية للأنشطة.
*   إدارة الوجبات اليومية وتتبع حالة تناول الأطفال لها.
*   إدارة الإعلانات والتعميمات.
*   إدارة الفعاليات والرحلات وتسجيل الأطفال بها.
*   إدارة المصادر التعليمية.
*   إدارة الوسائط (الصور والفيديو) وربطها بالأطفال أو الفصول أو الفعاليات.
*   إدارة سجلات الحضور والغياب.
*   إدارة السجلات الصحية للأطفال.
*   نظام رسائل بين الإدارة وأولياء الأمور.
*   نظام ملاحظات من أولياء الأمور للإدارة.

## التقنيات المستخدمة

*   **Backend:** Laravel Framework (PHP)
*   **Database:** MySQL (أو قاعدة بيانات أخرى يدعمها Laravel)
*   **API Authentication:** Laravel Sanctum (Token Based)
*   **Frontend (Web):** Laravel Blade, Bootstrap 5 (كمثال)
*   **Frontend (Mobile):** Flutter (مستهدف لواجهة API)

## تثبيت وإعداد المشروع (للمطورين)

1.  **نسخ المستودع:**
    ```bash
    git clone https://github.com/abdalganih1/kindergarten-backend kindergarten-backend
    cd kindergarten-backend
    ```
2.  **تثبيت الاعتماديات:**
    ```bash
    composer install
    npm install # (إذا كنت ستستخدم واجهات الويب)
    ```
3.  **إنشاء ملف البيئة:**
    ```bash
    cp .env.example .env
    ```
4.  **توليد مفتاح التطبيق:**
    ```bash
    php artisan key:generate
    ```
5.  **إعداد قاعدة البيانات:**
    *   أنشئ قاعدة بيانات جديدة (مثل `kindergarten_db`).
    *   عدّل معلومات الاتصال بقاعدة البيانات في ملف `.env`:
        ```dotenv
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=kindergarten_db
        DB_USERNAME=root
        DB_PASSWORD=your_db_password
        ```
6.  **تشغيل الهجرات (Migrations) وملء البيانات الأولية (Seeders):**
    ```bash
    php artisan migrate:fresh --seed
    ```
7.  **ربط مجلد التخزين (Storage Link):** (مهم لعرض الصور والملفات المرفوعة)
    ```bash
    php artisan storage:link
    ```
8.  **(لواجهة الويب فقط) بناء الأصول:**
    ```bash
    npm run dev # أو npm run build للإنتاج
    ```
9.  **تشغيل خادم التطوير:**
    ```bash
    php artisan serve
    ```
    سيكون التطبيق متاحًا على `http://localhost:8000` وواجهة API على `http://localhost:8000/api`.

## توثيق واجهة برمجة التطبيقات (API Documentation)

**الإصدار:** 1.0
**Base URL (مثال محلي):** `http://localhost:8000/api`
**Base URL (المستضاف):** `https://bisque-bear-644012.hostingersite.com/api`

### مقدمة

توفر واجهة برمجة التطبيقات هذه (API) نقاط نهاية (endpoints) لتطبيق Flutter الخاص بأولياء الأمور للتفاعل مع نظام إدارة رياض الأطفال. يتم استخدام مصادقة Sanctum المستندة إلى التوكن، ويتم إرجاع جميع الردود بتنسيق JSON.

**ملاحظات هامة لمطوري Flutter:**

*   **الهيدرات (Headers):** يجب إرسال `Accept: application/json` مع جميع الطلبات. أرسل `Content-Type: application/json` مع طلبات `POST` و `PUT`.
*   **المصادقة:** استخدم `Bearer Token` في هيدر `Authorization` لجميع الطلبات المحمية بعد تسجيل الدخول.
*   **الـ Base URL:** استخدم الرابط المستضاف `https://bisque-bear-644012.hostingersite.com/api` عند بناء التطبيق النهائي.
*   **الأمان:** استخدم `flutter_secure_storage` لتخزين التوكن بأمان. استخدم HTTPS دائمًا.
*   **حزم Flutter:** حزم `http` أو `dio` مفيدة لإجراء طلبات الشبكة. حزمة `json_serializable` مفيدة لتحويل JSON إلى كائنات Dart.

---

### قسم المصادقة والملف الشخصي (Authentication & Profile)

**1. تسجيل الدخول (Login)**

هذه هي الخطوة الأولى للحصول على توكن المصادقة.

*   **الطريقة (Method):** `POST`
*   **نقطة النهاية (Endpoint):** `/login`
*   **الوصف:** تسجيل دخول ولي أمر موجود وإرجاع توكن للوصول للـ API وبيانات المستخدم.
*   **المصادقة:** لا يوجد (نقطة نهاية عامة).
*   **بيانات الطلب (Request Body - JSON):**
    *   `email` (string, **required**): البريد الإلكتروني لولي الأمر (مثال من البيانات الأولية: `parent1@example.com`).
    *   `password` (string, **required**): كلمة المرور (مثال من البيانات الأولية: `password`).
    *   `device_name` (string, **required**): اسم مميز لتطبيقك أو جهاز المستخدم (مثال: `MyFlutterApp` أو `Parent_Ahmed_Device`). هذا يساعد في إدارة التوكنات.
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/login \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "email": "parent1@example.com",
            "password": "password",
            "device_name": "MyFlutterApp_Parent1"
          }'
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "token": "3|abcdefghijklmnopqrstuvwxyz...",
        "user": {
            "id": 2,
            "name": "ولي أمر ١",
            "email": "parent1@example.com",
            "email_verified_at": null,
            "role": "Parent",
            "is_active": true,
            "created_at": "2024-05-20T10:00:00.000000Z",
            "updated_at": "2024-05-20T10:00:00.000000Z",
            "parent_profile": {
                "parent_id": 1,
                "user_id": 2,
                "full_name": "ولي أمر ١",
                "contact_email": "parent1@example.com",
                "contact_phone": "987-654-3210",
                "address": "123 Main St, Anytown",
                "created_at": "2024-05-20T10:01:00.000000Z",
                "updated_at": "2024-05-20T10:01:00.000000Z"
            }
        }
    }
    ```
*   **الردود الخاطئة الشائعة:**
    *   `422 Unprocessable Entity`: خطأ في البيانات المُرسلة.
        ```json
        {
            "message": "The given data was invalid.",
            "errors": {
                "email": [
                    "These credentials do not match our records."
                ]
            }
        }
        ```
    *   `403 Forbidden`: الحساب غير نشط.

**2. تسجيل حساب ولي أمر جديد (إذا كان مفعلًا)**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/register`
*   **الوصف:** إنشاء حساب مستخدم وولي أمر جديدين.
*   **المصادقة:** لا يوجد.
*   **بيانات الطلب (JSON):**
    *   `name` (string, required)
    *   `email` (string, required, unique)
    *   `password` (string, required, min:8, confirmed)
    *   `password_confirmation` (string, required)
    *   `device_name` (string, required)
    *   `contact_phone` (string, optional)
    *   `address` (string, optional)
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/register \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
            "name": "ولي أمر جديد",
            "email": "newparent@example.com",
            "password": "password123",
            "password_confirmation": "password123",
            "contact_phone": "1231231234",
            "address": "789 New Street",
            "device_name": "FlutterApp_NewParent"
          }'
    ```
*   **مثال الرد الناجح (201 Created):** (مشابه لرد تسجيل الدخول)
*   **الردود الخاطئة الشائعة:** `422` (أخطاء التحقق).

**3. استخدام التوكن للمصادقة**

*   **Header:** `Authorization: Bearer YOUR_API_TOKEN`

**4. الحصول على بيانات المستخدم الحالي**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/user`
*   **المصادقة:** مطلوبة (`Bearer Token`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/user \
      -H "Authorization: Bearer YOUR_API_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource`)
*   **الردود الخاطئة الشائعة:** `401 Unauthorized`.

**5. تسجيل الخروج**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/logout`
*   **المصادقة:** مطلوبة (`Bearer Token`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/logout \
      -H "Authorization: Bearer YOUR_API_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** `{ "message": "Successfully logged out" }`
*   **الردود الخاطئة الشائعة:** `401 Unauthorized`.

**6. عرض ملف ولي الأمر الشخصي**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/profile`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/profile \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource` مع `parentProfile`)
*   **الردود الخاطئة الشائعة:** `401`, `403`.

**7. تحديث ملف ولي الأمر الشخصي**

*   **الطريقة:** `PUT`
*   **نقطة النهاية:** `/profile`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **بيانات الطلب (JSON):** `name` (optional), `contact_email` (optional), `contact_phone` (optional), `address` (optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT https://bisque-bear-644012.hostingersite.com/api/profile \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "contact_phone": "555-123-4567" }'
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource` المحدثة)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422`.

**8. تحديث كلمة مرور ولي الأمر**

*   **الطريقة:** `PUT`
*   **نقطة النهاية:** `/profile/password`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **بيانات الطلب (JSON):** `current_password` (required), `password` (required, confirmed), `password_confirmation` (required).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT https://bisque-bear-644012.hostingersite.com/api/profile/password \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "current_password": "password", "password": "newPassword123", "password_confirmation": "newPassword123" }'
    ```
*   **مثال الرد الناجح (200 OK):** `{ "message": "Password updated successfully." }`
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422`.

---

### قسم أولياء الأمور (Parent Specific Routes)

**جميع هذه المسارات تتطلب مصادقة (`Bearer Token`) ودور `Parent`.**

**9. عرض أطفال ولي الأمر**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/children`
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/children \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    { "data": [ /* Array of ChildResource */ ] }
    ```
*   **الردود الخاطئة الشائعة:** `401`, `403`.

**10. عرض تفاصيل طفل محدد**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/children/{child}`
*   **مُعامل المسار:** `child` (integer, required): ID الطفل.
*   **مثال الطلب (cURL - لولي أمر 1، يطلب الطفل 2):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/children/2 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    { "data": { /* ChildResource with loaded relations */ } }
    ```
*   **الردود الخاطئة الشائعة:** `401`, `403`, `404`.

**11. عرض الجداول الأسبوعية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/schedules`
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/schedules \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (مصفوفة من `WeeklyScheduleResource`)

**12. عرض السجلات الصحية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/health-records`
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/health-records \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (مصفوفة من `HealthRecordResource`)

**13. عرض الوجبات اليومية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/meals`
*   **مُعامل الاستعلام:** `date` (string, optional, `YYYY-MM-DD`).
*   **مثال الطلب (cURL - لليوم الحالي):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/meals \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (مصفوفة من `DailyMealResource`)

**14. عرض حالات وجبات الأطفال**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/meal-statuses`
*   **مُعاملات الاستعلام:** `date` (optional), `child_id` (optional), `per_page` (optional).
*   **مثال الطلب (cURL - لولي أمر 1، طفل 2، تاريخ محدد):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/meal-statuses?date=2024-03-11&child_id=2" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على `ChildMealStatusResource`)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422`.

**15. عرض الإعلانات**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/announcements`
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/announcements \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (مصفوفة من `AnnouncementResource`)

**16. عرض الوسائط**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/media`
*   **مُعامل الاستعلام:** `per_page` (optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/media?per_page=10" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على `MediaResource`)

**17. عرض الفعاليات**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/events`
*   **مُعامل الاستعلام:** `per_page` (optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/events?per_page=5" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على `EventResource`)

**18. عرض تفاصيل فعالية محددة**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/events/{event}`
*   **مُعامل المسار:** `event` (integer, required).
*   **مثال الطلب (cURL - لفعالية 1):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/events/1 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن `EventResource` مع `meta`)

**19. تسجيل طفل في فعالية**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/events/{event}/register`
*   **مُعامل المسار:** `event` (integer, required).
*   **بيانات الطلب (JSON):** `child_id` (required), `parent_consent` (optional).
*   **مثال الطلب (cURL - ولي أمر 1 يسجل الطفل 1 في فعالية 1):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/events/1/register \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "child_id": 1, "parent_consent": true }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `EventRegistrationResource`)
*   **الردود الخاطئة الشائعة:** `400`, `401`, `403`, `404`, `409`, `422`.

**20. إلغاء التسجيل في فعالية**

*   **الطريقة:** `DELETE`
*   **نقطة النهاية:** `/event-registrations/{registration}`
*   **مُعامل المسار:** `registration` (integer, required).
*   **مثال الطلب (cURL - ولي أمر 1 يلغي التسجيل رقم 3):**
    ```bash
    curl -X DELETE https://bisque-bear-644012.hostingersite.com/api/event-registrations/3 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** `{ "message": "Registration cancelled successfully." }`
*   **الردود الخاطئة الشائعة:** `401`, `403`, `404`.

**21. عرض المصادر التعليمية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/educational-resources`
*   **مُعاملات الاستعلام:** `per_page` (optional), `subject` (optional), `age` (optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/educational-resources?age=4" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على `EducationalResource`)

**22. إرسال ملاحظة**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/observations`
*   **بيانات الطلب (JSON):** `observation_text` (required), `child_id` (optional).
*   **مثال الطلب (cURL - ولي أمر 1 يرسل ملاحظة عن الطفل 2):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/observations \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "observation_text": "فاطمة كانت سعيدة اليوم.", "child_id": 2 }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `ObservationResource`)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422`.

**23. عرض الرسائل**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/messages`
*   **مُعامل الاستعلام:** `per_page` (optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/messages?per_page=10" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على `MessageResource`)

**24. إرسال رسالة**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/messages`
*   **بيانات الطلب (JSON):** `recipient_id` (required, Admin/Supervisor ID), `subject` (optional), `body` (required).
*   **مثال الطلب (cURL - ولي أمر 1 يرسل للمدير 1):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/messages \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "recipient_id": 1, "subject": "استفسار هام", "body": "نص الاستفسار هنا..." }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `MessageResource`)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422`.

**25. عرض رسالة محددة**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/messages/{message}`
*   **مُعامل المسار:** `message` (integer, required): ID الرسالة.
*   **مثال الطلب (cURL - ولي أمر 1 يطلب الرسالة 10):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/messages/10 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن `MessageResource`)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `404`.

---

### الأخطاء الشائعة (Common Error Codes)

*   `401 Unauthorized`
*   `403 Forbidden`
*   `404 Not Found`
*   `422 Unprocessable Entity`
*   `409 Conflict`
*   `500 Internal Server Error`

---

## المساهمة (Contributing)

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).