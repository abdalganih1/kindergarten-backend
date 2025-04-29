# نظام إدارة رياض الأطفال - الواجهة الخلفية (Backend)

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

هذا المستودع يحتوي على الواجهة الخلفية (Backend) لتطبيق نظام إدارة رياض الأطفال، المبني باستخدام إطار العمل Laravel. يوفر هذا الـ Backend واجهة برمجة تطبيقات (API) لتطبيق الواجهة الأمامية (Flutter) المستخدم من قبل أولياء الأمور.

## توثيق واجهة برمجة التطبيقات (API Documentation)

**الإصدار:** 1.0
**Base URL:** `https://bisque-bear-644012.hostingersite.com/api`

### مقدمة

أهلاً بك في توثيق الـ API الخاص بنظام إدارة رياض الأطفال! هذا الدليل مخصص لمطوري Flutter لمساعدتهم على فهم كيفية التفاعل مع الواجهة الخلفية لجلب البيانات وإرسالها.

*   **التنسيق:** جميع الطلبات والردود تستخدم تنسيق JSON.
*   **المصادقة:** نستخدم نظام Laravel Sanctum للمصادقة بواسطة التوكن (Bearer Token).
*   **الهيدرات (Headers):** يجب على **جميع** طلبات API إرسال الهيدرات التالية:
    *   `Accept: application/json`
    *   `Content-Type: application/json` (للطلبات التي ترسل بيانات مثل POST, PUT)

### المصادقة (Authentication)

قبل أن تتمكن من الوصول إلى معظم ميزات الـ API، تحتاج إلى مصادقة المستخدم (ولي الأمر) والحصول على توكن.

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
        "token": "3|abcdefghijklmnopqrstuvwxyz...", // <-- هذا هو التوكن الذي ستحفظه وتستخدمه
        "user": {
            "id": 2, // معرف المستخدم العام
            "name": "ولي أمر ١",
            "email": "parent1@example.com",
            "email_verified_at": null, // قد يكون null أو تاريخ
            "role": "Parent",
            "is_active": true,
            "created_at": "2024-05-20T10:00:00.000000Z", // مثال للتاريخ
            "updated_at": "2024-05-20T10:00:00.000000Z", // مثال للتاريخ
            "parent_profile": { // بيانات ملف ولي الأمر الإضافية
                "parent_id": 1, // معرف ولي الأمر المحدد
                "user_id": 2,
                "full_name": "ولي أمر ١",
                "contact_email": "parent1@example.com",
                "contact_phone": "987-654-3210",
                "address": "123 Main St, Anytown",
                "created_at": "2024-05-20T10:01:00.000000Z",
                "updated_at": "2024-05-20T10:01:00.000000Z"
            }
            // admin_profile سيكون null لولي الأمر
        }
    }
    ```
*   **الردود الخاطئة الشائعة:**
    *   `422 Unprocessable Entity`: خطأ في البيانات المُرسلة (مثل بريد إلكتروني غير موجود، كلمة مرور خاطئة، حقل `device_name` مفقود). تفحص حقل `errors` في الرد لمعرفة التفاصيل.
        ```json
        {
            "message": "The given data was invalid.",
            "errors": {
                "email": [
                    "These credentials do not match our records." // أو رسالة خطأ تحقق أخرى
                ]
            }
        }
        ```
    *   `403 Forbidden`: الحساب غير نشط.

**ملاحظة لمطوري Flutter:**
استخدم حزمة مثل `http` أو `dio`. عند تسجيل الدخول بنجاح، احفظ قيمة `token` بأمان (مثل استخدام `flutter_secure_storage`). ستحتاج لإرسال هذا التوكن في هيدر `Authorization` لجميع الطلبات التالية التي تتطلب مصادقة.

```dart
// مثال مبسط باستخدام حزمة http
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<String?> login(String email, String password, String deviceName) async {
  final url = Uri.parse('https://bisque-bear-644012.hostingersite.com/api/login');
  try {
    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_name': deviceName,
      }),
    );

    if (response.statusCode == 200) {
      final responseData = jsonDecode(response.body);
      // احفظ التوكن وبيانات المستخدم
      String token = responseData['token'];
      // print('Login successful! Token: $token');
      // print('User data: ${responseData['user']}');
      return token;
    } else {
      // print('Login failed: ${response.statusCode}');
      // print('Error body: ${response.body}');
      return null;
    }
  } catch (error) {
    // print('Login error: $error');
    return null;
  }
}
```

**2. تسجيل حساب ولي أمر جديد (إذا كان مفعلًا)**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/register`
*   **الوصف:** إنشاء حساب مستخدم وحساب ولي أمر جديدين.
*   **المصادقة:** لا يوجد.
*   **بيانات الطلب (JSON):**
    *   `name` (string, required): الاسم الكامل لولي الأمر.
    *   `email` (string, required, unique): البريد الإلكتروني (سيكون فريدًا).
    *   `password` (string, required, min:8, confirmed): كلمة المرور (يجب أن تكون 8 أحرف على الأقل وتطابق `password_confirmation`).
    *   `password_confirmation` (string, required): تأكيد كلمة المرور.
    *   `device_name` (string, required): اسم الجهاز.
    *   `contact_phone` (string, optional): رقم الهاتف.
    *   `address` (string, optional): العنوان.
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
*   **مثال الرد الناجح (201 Created):** (مشابه لرد تسجيل الدخول، مع بيانات المستخدم الجديد)
*   **الردود الخاطئة الشائعة:** `422 Unprocessable Entity` (أخطاء التحقق: بريد مستخدم، كلمة مرور ضعيفة، إلخ).

**3. استخدام التوكن للمصادقة**

في جميع الطلبات التالية التي تتطلب مصادقة، يجب إضافة هيدر `Authorization`.

*   **Header:** `Authorization: Bearer YOUR_API_TOKEN`
    (استبدل `YOUR_API_TOKEN` بالتوكن الذي حصلت عليه من `/login` أو `/register`)

**4. الحصول على بيانات المستخدم الحالي**

للتحقق من أن التوكن لا يزال صالحًا وللحصول على بيانات المستخدم الحالية (بما في ذلك ملف ولي الأمر).

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/user`
*   **المصادقة:** مطلوبة (`Bearer Token`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/user \
      -H "Authorization: Bearer YOUR_API_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource` كما في رد تسجيل الدخول)
*   **الردود الخاطئة الشائعة:** `401 Unauthorized` (التوكن غير صالح أو مفقود).

**5. تسجيل الخروج**

لإبطال التوكن الحالي ومنع استخدامه مرة أخرى.

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/logout`
*   **المصادقة:** مطلوبة (`Bearer Token`).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/logout \
      -H "Authorization: Bearer YOUR_API_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    { "message": "Successfully logged out" }
    ```
*   **الردود الخاطئة الشائعة:** `401 Unauthorized`.

---

### إدارة الملف الشخصي لولي الأمر (Parent Profile)

**جميع هذه المسارات تتطلب مصادقة (`Bearer Token`) ودور `Parent`.**

**6. عرض ملف ولي الأمر**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/profile`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/profile \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource` كاملة مع `parentProfile`)
*   **الردود الخاطئة الشائعة:** `401 Unauthorized`, `403 Forbidden`.

**7. تحديث ملف ولي الأمر**

*   **الطريقة:** `PUT`
*   **نقطة النهاية:** `/profile`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **بيانات الطلب (JSON):** (أرسل فقط الحقول المراد تحديثها)
    *   `name` (string, optional)
    *   `contact_email` (string, optional, email)
    *   `contact_phone` (string, optional)
    *   `address` (string, optional)
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT https://bisque-bear-644012.hostingersite.com/api/profile \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "contact_phone": "555-123-4567" }'
    ```
*   **مثال الرد الناجح (200 OK):** (بيانات `UserResource` المحدثة)
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422 Unprocessable Entity` (أخطاء التحقق).

**8. تحديث كلمة مرور ولي الأمر**

*   **الطريقة:** `PUT`
*   **نقطة النهاية:** `/profile/password`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **بيانات الطلب (JSON):**
    *   `current_password` (string, required)
    *   `password` (string, required, confirmed)
    *   `password_confirmation` (string, required)
*   **مثال الطلب (cURL):**
    ```bash
    curl -X PUT https://bisque-bear-644012.hostingersite.com/api/profile/password \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "current_password": "password", "password": "newPassword123", "password_confirmation": "newPassword123" }'
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    { "message": "Password updated successfully." }
    ```
*   **الردود الخاطئة الشائعة:** `401`, `403`, `422` (كلمة مرور حالية خاطئة، عدم تطابق التأكيد، كلمة مرور جديدة ضعيفة).

---

### بيانات الأطفال (Children Data)

**جميع هذه المسارات تتطلب مصادقة (`Bearer Token`) ودور `Parent`.**

**9. عرض قائمة أطفال ولي الأمر**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/children`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/children \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (يفترض أن ولي أمر 1 مرتبط بالطفل 1 و 2)
    ```json
    {
        "data": [
            {
                "child_id": 1,
                "first_name": "أحمد",
                "last_name": "علي",
                "full_name": "أحمد علي",
                "date_of_birth": "2022-05-15",
                "gender": "Male",
                "enrollment_date": "2024-01-10",
                "allergies": "حساسية الفول السوداني",
                "medical_notes": "لا يوجد",
                "photo_url": null, // أو رابط الصورة
                "created_at": "...",
                "class": {
                    "class_id": 1, // معرف فصل أحمد
                    "class_name": "الأطفال الصغار (أقل من 3)",
                    // ...
                },
                "parents": [ /* ... */ ],
                "health_records": [ /* ... */ ],
                "attendances": [ /* ... */ ]
            },
            {
                "child_id": 2,
                "first_name": "فاطمة",
                "last_name": "محمد",
                "full_name": "فاطمة محمد",
                "date_of_birth": "2020-11-20",
                "gender": "Female",
                "enrollment_date": "2024-01-10",
                "allergies": null,
                "medical_notes": "تحتاج نظارة للقراءة",
                "photo_url": null,
                "created_at": "...",
                "class": {
                    "class_id": 2, // معرف فصل فاطمة
                    "class_name": "مرحلة ما قبل المدرسة (3-6)",
                     // ...
                },
                "parents": [ /* ... */ ],
                "health_records": [ /* ... */ ],
                "attendances": [ /* ... */ ]
            }
        ]
    }
    ```
*   **الردود الخاطئة الشائعة:** `401 Unauthorized`, `403 Forbidden`.

**10. عرض تفاصيل طفل محدد**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/children/{child}`
*   **المصادقة:** مطلوبة (`Bearer Token`, Role: Parent).
*   **مُعامل المسار (Path Parameter):**
    *   `child` (integer, required): معرف الطفل (`child_id`).
*   **مثال الطلب (cURL - لولي أمر 1، يطلب بيانات الطفل 2):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/children/2 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": {
            "child_id": 2,
            "first_name": "فاطمة",
            // ... بقية بيانات ChildResource مع تحميل العلاقات (class, healthRecords, attendances, etc.) ...
        }
    }
    ```
*   **الردود الخاطئة الشائعة:** `401`, `403` (إذا حاول ولي أمر 2 طلب بيانات الطفل 2 باستخدام توكن ولي أمر 1), `404` (إذا كان ID الطفل غير موجود).

---

### البيانات المتعلقة بالروضة (Kindergarten Related Data)

**جميع هذه المسارات تتطلب مصادقة (`Bearer Token`) ودور `Parent`.**

**11. عرض الجداول الأسبوعية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/schedules`
*   **الوصف:** يعرض الجدول الأسبوعي للفصول التي ينتمي إليها أطفال ولي الأمر الحالي.
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
*   **الوصف:** يعرض السجلات الصحية الخاصة بأطفال ولي الأمر الحالي.
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
*   **الوصف:** يعرض الوجبات المتاحة (العامة أو الخاصة بفصل أطفال ولي الأمر) لتاريخ معين (الافتراضي هو اليوم).
*   **مُعامل الاستعلام (Query Parameter):**
    *   `date` (string, optional, `YYYY-MM-DD`)
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
*   **الوصف:** يعرض كيف تناول أطفال ولي الأمر وجباتهم في تاريخ معين (الافتراضي هو اليوم). يمكن فلترته لطفل معين.
*   **مُعاملات الاستعلام (Query Parameters):**
    *   `date` (string, optional, `YYYY-MM-DD`)
    *   `child_id` (integer, optional)
    *   `per_page` (integer, optional)
*   **مثال الطلب (cURL - لولي أمر 1، طفل 2، تاريخ محدد):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/meal-statuses?date=2024-03-11&child_id=2" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):**
    ```json
    {
        "data": [
            { // ChildMealStatusResource
                "status_id": 1,
                "consumption_status": "EatenSome", // مثال
                "status_text": "أكل البعض",
                "notes": "لم تكمل الخضروات", // مثال
                "recorded_at": "...",
                "meal": { // DailyMealResource
                    "meal_id": 10,
                    "meal_date": "2024-03-11",
                    "meal_type": "Lunch",
                     // ...
                 },
                "child": { // ChildResource (minimal)
                     "child_id": 2,
                     "full_name": "فاطمة محمد"
                 }
            },
            // ... other meal statuses for that child/day
        ],
        "links": { /* ... */ },
        "meta": { /* ... */ }
    }
    ```
*   **الردود الخاطئة الشائعة:** 401, 403, 422 (تنسيق تاريخ خاطئ، `child_id` غير مصرح به).

**15. عرض الإعلانات**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/announcements`
*   **الوصف:** يعرض الإعلانات العامة والإعلانات الموجهة لفصول أطفال ولي الأمر.
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
*   **الوصف:** يعرض الوسائط ذات الصلة بولي الأمر (الأطفال، الفصول، الفعاليات، عامة) مع pagination.
*   **مُعامل الاستعلام:** `per_page` (integer, optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/media?per_page=10" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على مصفوفة `data` من `MediaResource`)

**17. عرض الفعاليات**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/events`
*   **الوصف:** يعرض الفعاليات القادمة مع pagination.
*   **مُعامل الاستعلام:** `per_page` (integer, optional).
*   **مثال الطلب (cURL):**
    ```bash
    curl -X GET "https://bisque-bear-644012.hostingersite.com/api/events?per_page=5" \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن pagination يحتوي على مصفوفة `data` من `EventResource`)

**18. عرض تفاصيل فعالية**

*   **الطريقة:** `GET`
*   **نقطة النهاية:** `/events/{event}`
*   **مُعامل المسار:** `event` (integer, required): ID الفعالية.
*   **مثال الطلب (cURL - لفعالية 1):**
    ```bash
    curl -X GET https://bisque-bear-644012.hostingersite.com/api/events/1 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** (كائن `EventResource` مع قسم `meta` إضافي)

**19. تسجيل طفل في فعالية**

*   **الطريقة:** `POST`
*   **نقطة النهاية:** `/events/{event}/register`
*   **مُعامل المسار:** `event` (integer, required): ID الفعالية.
*   **بيانات الطلب (JSON):** `child_id` (integer, required), `parent_consent` (boolean, optional).
*   **مثال الطلب (cURL - ولي أمر 1 يسجل الطفل 1 في فعالية 1):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/events/1/register \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "child_id": 1, "parent_consent": true }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `EventRegistrationResource`)
*   **الردود الخاطئة الشائعة:** 400, 401, 403, 404, 409, 422.

**20. إلغاء التسجيل في فعالية**

*   **الطريقة:** `DELETE`
*   **نقطة النهاية:** `/event-registrations/{registration}`
*   **مُعامل المسار:** `registration` (integer, required): ID التسجيل (وليس ID الفعالية أو الطفل).
*   **مثال الطلب (cURL - ولي أمر 1 يلغي التسجيل رقم 3):**
    ```bash
    curl -X DELETE https://bisque-bear-644012.hostingersite.com/api/event-registrations/3 \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Accept: application/json"
    ```
*   **مثال الرد الناجح (200 OK):** `{ "message": "Registration cancelled successfully." }`
*   **الردود الخاطئة الشائعة:** 401, 403, 404.

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
*   **بيانات الطلب (JSON):** `observation_text` (string, required), `child_id` (integer, optional).
*   **مثال الطلب (cURL - ولي أمر 1 يرسل ملاحظة عن الطفل 2):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/observations \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "observation_text": "فاطمة كانت سعيدة اليوم.", "child_id": 2 }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `ObservationResource`)
*   **الردود الخاطئة الشائعة:** 401, 403, 422.

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
*   **بيانات الطلب (JSON):** `recipient_id` (integer, required, Admin/Supervisor ID), `subject` (string, optional), `body` (string, required).
*   **مثال الطلب (cURL - ولي أمر 1 يرسل للمدير 1):**
    ```bash
    curl -X POST https://bisque-bear-644012.hostingersite.com/api/messages \
      -H "Authorization: Bearer YOUR_PARENT_TOKEN_FOR_PARENT_1" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{ "recipient_id": 1, "subject": "استفسار هام", "body": "نص الاستفسار هنا..." }'
    ```
*   **مثال الرد الناجح (201 Created):** (كائن `MessageResource`)
*   **الردود الخاطئة الشائعة:** 401, 403, 422.

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
*   **الردود الخاطئة الشائعة:** 401, 403 (ليست رسالة المستخدم), 404.

---

### ملاحظات لمطوري Flutter

*   **إدارة الحالة (State Management):** استخدم حل إدارة حالة مناسب (Provider, Riverpod, Bloc, GetX) لتخزين التوكن وبيانات المستخدم وتحديث الواجهات عند تغير البيانات.
*   **معالجة الأخطاء:** قم بمعالجة رموز الحالة المختلفة (401, 403, 404, 422, 5xx) بشكل مناسب في تطبيقك لعرض رسائل خطأ واضحة للمستخدم أو محاولة إعادة المصادقة.
*   **الطلبات المتزامنة (Asynchronous Requests):** جميع طلبات الشبكة غير متزامنة. استخدم `async`/`await` وقم بعرض مؤشرات تحميل (loading indicators) للمستخدم أثناء انتظار الرد.
*   **تحليل JSON:** استخدم `dart:convert` لتحويل ردود JSON إلى كائنات Dart (Models) لتسهيل التعامل مع البيانات. يمكن استخدام أدوات مثل `json_serializable` لتوليد هذا الكود تلقائيًا.
*   **الأمان:** لا تخزن التوكن في أماكن غير آمنة. استخدم `flutter_secure_storage`. تأكد من استخدام HTTPS دائمًا للاتصال بالـ API.

---
---

## إعادة بناء ملف README.md

```markdown
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

**(انسخ والصق هنا تفاصيل نقاط النهاية من 1 إلى 8 من الرد السابق)**
*(تأكد من تحديث Base URL في أمثلة cURL لتستخدم الرابط المستضاف)*
*   1. تسجيل الدخول (POST /login)
*   2. تسجيل حساب جديد (POST /register) - *إذا كان مفعلًا*
*   3. استخدام التوكن للمصادقة
*   4. الحصول على بيانات المستخدم الحالي (GET /user)
*   5. تسجيل الخروج (POST /logout)
*   6. عرض ملف ولي الأمر الشخصي (GET /profile)
*   7. تحديث ملف ولي الأمر الشخصي (PUT /profile)
*   8. تحديث كلمة مرور ولي الأمر (PUT /profile/password)

---

### قسم أولياء الأمور (Parent Specific Routes)

**(انسخ والصق هنا تفاصيل نقاط النهاية من 9 إلى 25 من الرد السابق)**
*(تأكد من تحديث Base URL في أمثلة cURL وتحديث معرفات الأطفال/الفعاليات لتطابق البيانات الأولية)*
*   9. عرض أطفال ولي الأمر (GET /children)
*   10. عرض تفاصيل طفل محدد (GET /children/{child})
*   11. عرض الجداول الأسبوعية (GET /schedules)
*   12. عرض السجلات الصحية (GET /health-records)
*   13. عرض الوجبات اليومية (GET /meals)
*   14. عرض حالات وجبات الأطفال (GET /meal-statuses)
*   15. عرض الإعلانات (GET /announcements)
*   16. عرض الوسائط (GET /media)
*   17. عرض الفعاليات (GET /events)
*   18. عرض تفاصيل فعالية محددة (GET /events/{event})
*   19. تسجيل طفل في فعالية (POST /events/{event}/register)
*   20. إلغاء التسجيل في فعالية (DELETE /event-registrations/{registration})
*   21. عرض المصادر التعليمية (GET /educational-resources)
*   22. إرسال ملاحظة (POST /observations)
*   23. عرض الرسائل (GET /messages)
*   24. إرسال رسالة (POST /messages)
*   25. عرض رسالة محددة (GET /messages/{message})

---

### الأخطاء الشائعة (Common Error Codes)

*   **401 Unauthorized:** التوكن مفقود، غير صالح، أو منتهي الصلاحية.
*   **403 Forbidden:** المستخدم ليس لديه الصلاحية للوصول (خطأ في الدور أو الملكية).
*   **404 Not Found:** المورد المطلوب (مثل `/children/999`) غير موجود.
*   **422 Unprocessable Entity:** خطأ في التحقق من صحة البيانات المرسلة (راجع مصفوفة `errors` في الرد).
*   **409 Conflict:** محاولة إنشاء مورد موجود بالفعل.
*   **500 Internal Server Error:** خطأ عام في الخادم.

---

## المساهمة (Contributing)

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

```

