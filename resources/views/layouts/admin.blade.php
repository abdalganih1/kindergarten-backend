<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl"> {{-- دعم RTL --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'روضتي') }} - لوحة تحكم المدير</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"> {{-- خط Cairo كمثال --}}

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous"> {{-- Bootstrap RTL --}}
    {{-- <link href="{{ asset('css/admin-custom.css') }}" rel="stylesheet"> --}} {{-- يمكنك إنشاء ملف CSS مخصص --}}

    <style>
        /* تنسيقات أساسية ومخصصة */
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa; /* خلفية رمادية فاتحة */
            padding-top: 56px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            /* rtl:raw:
            right: 0;
            */
            /* ltr:raw:
            left: 0;
            */
            bottom: 0;
            right: 0; /* For RTL */
            z-index: 100; /* خلف شريط التنقل إذا كان ثابتًا */
            padding: 48px 0 0; /* ترك مسافة للشريط العلوي */
            box-shadow: inset 1px 0 0 rgba(0, 0, 0, .1); /* تغيير الظل لليمين في RTL */
            border-left: 1px solid #dee2e6; /* حدود واضحة */
            background-color: #fff; /* خلفية بيضاء للشريط الجانبي */
            width: 240px; /* عرض مناسب */
            transition: all 0.3s;
            overflow-y: auto; /* للسماح بالتمرير إذا زادت العناصر */
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
                border-left: none;
                border-bottom: 1px solid #dee2e6;
                padding-top: 15px; /* تقليل المسافة في الشاشات الصغيرة */
            }
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333; /* لون أغمق للروابط */
            padding: .75rem 1.5rem; /* زيادة المساحة الداخلية */
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link .feather {
            width: 18px; /* حجم الأيقونة */
            height: 18px;
            margin-left: 8px; /* مسافة بين الأيقونة والنص */
            vertical-align: text-bottom;
            color: #6c757d; /* لون الأيقونة */
        }

        .sidebar .nav-link.active {
            color: #0d6efd; /* لون الرابط النشط */
            background-color: #e7f1ff; /* خلفية خفيفة للرابط النشط */
            border-right: 3px solid #0d6efd; /* شريط جانبي للرابط النشط في RTL */
            padding-right: calc(1.5rem - 3px); /* ضبط الحشوة بسبب الحدود */
        }
         .sidebar .nav-link.active .feather {
             color: #0d6efd; /* لون الأيقونة النشطة */
         }

        .sidebar .nav-link:hover {
            background-color: #f1f1f1; /* تغيير الخلفية عند المرور */
        }

        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }

        /* تنسيق المحتوى الرئيسي */
        .content {
            transition: margin-right .3s ease-in-out; /* انتقال سلس لتغيير الهامش */
        }

        @media (min-width: 768px) {
            .content {
                margin-right: 240px; /* ترك مسافة للشريط الجانبي الثابت */
                margin-left: 0; /* ضمان عدم وجود هامش أيسر */
            }
             /* في حالة إخفاء الشريط الجانبي (تحتاج JS للتبديل) */
            body.sidebar-toggled .content {
                 margin-right: 0;
            }
             body.sidebar-toggled .sidebar {
                 margin-right: -240px; /* إخفاء الشريط الجانبي */
            }
        }

        .navbar-brand {
             padding-top: .75rem;
             padding-bottom: .75rem;
             font-size: 1rem;
             background-color: rgba(0, 0, 0, .25);
             box-shadow: inset 1px 0 0 rgba(0, 0, 0, .25);
        }

        .navbar .navbar-toggler {
             top: .25rem;
             right: 1rem; /* ضبط موضع زر التبديل في RTL */
        }

        .navbar .form-control {
             padding: .75rem 1rem;
        }

        .form-control-dark {
             color: #fff;
             background-color: rgba(255, 255, 255, .1);
             border-color: rgba(255, 255, 255, .1);
        }

        .form-control-dark:focus {
             border-color: transparent;
             box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        }

        /* تنسيق إضافي */
        .table th, .table td {
            vertical-align: middle;
            text-align: center; /* محاذاة النص في الوسط */
        }
         .table thead th {
             background-color: #343a40; /* خلفية داكنة لرأس الجدول */
             color: #fff;
         }
        .page-header {
             border-bottom: 1px solid #dee2e6;
        }

    </style>
</head>
<body>
    <div id="app">
        {{-- شريط التنقل العلوي --}}
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
            <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('admin.dashboard') }}">
                <span data-feather="slack" class="me-1"></span> {{ config('app.name', 'روضتي') }} - الإدارة
            </a>
             {{-- زر لتبديل الشريط الجانبي في الشاشات الصغيرة --}}
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="تبديل الشريط الجانبي">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- يمكن إضافة حقل بحث عام هنا --}}
             {{-- <input class="form-control form-control-dark w-100 rounded-0 border-0" type="text" placeholder="بحث..." aria-label="بحث"> --}}

            <div class="navbar-nav ms-auto"> {{-- استخدام ms-auto لوضع العناصر في اليمين (المرئي) في RTL --}}
                <div class="nav-item text-nowrap d-flex align-items-center">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link px-3" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register')) {{-- السماح بالتسجيل إذا كان المسار متاحًا --}}
                            <li class="nav-item">
                                <a class="nav-link px-3" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <div class="nav-item dropdown px-3">
                             <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <span data-feather="user" class="feather"></span> {{ Auth::user()->name }} ({{ Auth::user()->role }})
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                {{-- يمكنك إضافة رابط لملف المدير الشخصي هنا --}}
                                {{-- <a class="dropdown-item" href="#">الملف الشخصي</a> --}}
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    <span data-feather="log-out" class="feather me-1"></span> {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                {{-- الشريط الجانبي --}}
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <span data-feather="home"></span>
                                    لوحة التحكم الرئيسية
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}">
                                    <span data-feather="check-square"></span>
                                    الحضور والغياب
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.children.*') ? 'active' : '' }}" href="{{ route('admin.children.index') }}">
                                    <span data-feather="users"></span>
                                    إدارة الأطفال
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}">
                                    <span data-feather="layers"></span>
                                    إدارة الفصول
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}" href="{{ route('admin.users.index', ['role' => 'Parent']) }}"> {{-- مثال لرابط مخصص لأولياء الأمور --}}
                                    <span data-feather="briefcase"></span>
                                    إدارة أولياء الأمور
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.meals.*') ? 'active' : '' }}" href="{{ route('admin.meals.index') }}">
                                    <span data-feather="coffee"></span>
                                    إدارة الوجبات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">
                                    <span data-feather="bell"></span>
                                    إدارة الإعلانات
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}">
                                    <span data-feather="calendar"></span>
                                    إدارة الفعاليات
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.resources.*') ? 'active' : '' }}" href="{{ route('admin.resources.index') }}">
                                    <span data-feather="book-open"></span>
                                    المصادر التعليمية
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}">
                                    <span data-feather="image"></span>
                                    إدارة الوسائط
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}" href="{{ route('admin.messages.index') }}">
                                    <span data-feather="message-square"></span>
                                    الرسائل الواردة
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.observations.*') ? 'active' : '' }}" href="{{ route('admin.observations.index') }}">
                                    <span data-feather="eye"></span>
                                    ملاحظات أولياء الأمور
                                </a>
                            </li>
                        </ul>

                        {{-- قسم منفصل لإدارة النظام/المستخدمين --}}
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                          <span>إدارة النظام</span>
                        </h6>
                        <ul class="nav flex-column mb-2">
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <span data-feather="user"></span>
                                    إدارة المستخدمين
                                </a>
                            </li>
                             {{-- يمكن إضافة رابط للإعدادات هنا --}}
                             {{-- <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span data-feather="settings"></span>
                                    الإعدادات العامة
                                </a>
                            </li> --}}
                        </ul>
                    </div>
                </nav>

                {{-- المحتوى الرئيسي للصفحة --}}
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content pt-3">
                    {{-- تم نقل الهيدر والرسائل إلى هنا ليكونوا ضمن main --}}
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 page-header">
                        <h1 class="h2">@yield('title', 'لوحة التحكم')</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            @yield('header-buttons') {{-- مكان للأزرار الخاصة بكل صفحة (مثل زر إضافة جديد) --}}
                         </div>
                    </div>

                     {{-- عرض رسائل النجاح والخطأ --}}
                    @include('partials.alerts') {{-- تضمين ملف جزئي للرسائل --}}

                    @yield('content') {{-- هذا هو المكان الذي سيتم فيه حقن محتوى كل صفحة --}}

                    {{-- مثال للفوتر داخل المحتوى --}}
                     <footer class="pt-4 my-md-5 pt-md-5 border-top text-center text-muted">
                        جميع الحقوق محفوظة © {{ date('Y') }} {{ config('app.name', 'روضتي') }}
                     </footer>
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace({ // تفعيل الأيقونات مع ضبط الحجم واللون
          'stroke-width': 1.5,
           width: 20,
           height: 20
      });
    </script>
    {{-- <script src="{{ asset('js/admin-custom.js') }}"></script> --}} {{-- يمكنك إضافة ملف JS مخصص --}}
    @stack('scripts') {{-- للسماح بإضافة سكريبتات خاصة بكل صفحة --}}
</body>
</html>