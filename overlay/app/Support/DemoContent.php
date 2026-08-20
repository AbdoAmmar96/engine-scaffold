<?php

namespace App\Support;

/**
 * بيانات تجريبية للعرض فقط — أسماء المشاريع خيالية والأرقام واقعية للسوق.
 * الصور من Unsplash ومحمّلة محليًا في public/images/demo.
 * في المرحلة 4 الصفحات بتتوصل بموديلات Properties/Compounds بنفس شكل الـ props.
 */
class DemoContent
{
    public static function properties(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            [
                'id' => 1,
                'title' => $ar ? 'شقة 165م بجاردن خاصة في التجمع الخامس' : 'Apartment 165m with private garden in Fifth Settlement',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 4,850,000',
                'beds' => 3, 'baths' => 2, 'size' => 165,
                'ref' => 'XH-1001',
                'image' => '/images/demo/property-1.jpg',
            ],
            [
                'id' => 2,
                'title' => $ar ? 'فيلا مستقلة 420م تشطيب كامل' : 'Standalone villa 420m fully finished',
                'area' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 18,500,000',
                'beds' => 5, 'baths' => 5, 'size' => 420,
                'ref' => 'XH-1002',
                'image' => '/images/demo/property-2.jpg',
            ],
            [
                'id' => 3,
                'title' => $ar ? 'شقة بحرية 140م على الكورنيش' : 'Sea-view apartment 140m on the Corniche',
                'area' => $ar ? 'الإسكندرية' : 'Alexandria',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 6,200,000',
                'beds' => 3, 'baths' => 2, 'size' => 140,
                'ref' => 'XH-1003',
                'image' => '/images/demo/property-3.jpg',
            ],
            [
                'id' => 4,
                'title' => $ar ? 'شقة مفروشة للإيجار بالمطبخ والتكييفات' : 'Furnished apartment for rent with kitchen and ACs',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'purpose' => $ar ? 'إيجار' : 'Rent',
                'price' => $ar ? 'EGP 38,000 / شهريًا' : 'EGP 38,000 / mo',
                'beds' => 2, 'baths' => 2, 'size' => 156,
                'ref' => 'XH-1004',
                'image' => '/images/demo/property-4.jpg',
            ],
            [
                'id' => 5,
                'title' => $ar ? 'توين هاوس 280م استلام فوري' : 'Twin house 280m ready to move',
                'area' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 9,750,000',
                'beds' => 4, 'baths' => 3, 'size' => 280,
                'ref' => 'XH-1005',
                'image' => '/images/demo/property-5.jpg',
            ],
            [
                'id' => 6,
                'title' => $ar ? 'مكتب إداري 65م في برج مرخّص' : 'Office 65m in a licensed tower',
                'area' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 3,900,000',
                'beds' => 0, 'baths' => 1, 'size' => 65,
                'ref' => 'XH-1006',
                'image' => '/images/demo/property-6.jpg',
            ],
            [
                'id' => 7,
                'title' => $ar ? 'دوبلكس 235م بروف خاص' : 'Duplex 235m with private roof',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 7,400,000',
                'beds' => 4, 'baths' => 3, 'size' => 235,
                'ref' => 'XH-1007',
                'image' => '/images/demo/property-7.jpg',
            ],
            [
                'id' => 8,
                'title' => $ar ? 'استوديو 78م مفروش بالكامل للإيجار' : 'Fully furnished studio 78m for rent',
                'area' => $ar ? 'الإسكندرية' : 'Alexandria',
                'purpose' => $ar ? 'إيجار' : 'Rent',
                'price' => $ar ? 'EGP 19,500 / شهريًا' : 'EGP 19,500 / mo',
                'beds' => 1, 'baths' => 1, 'size' => 78,
                'ref' => 'XH-1008',
                'image' => '/images/demo/property-8.jpg',
            ],
            [
                'id' => 9,
                'title' => $ar ? 'شقة 190م بفيو لاندسكيب مفتوح' : 'Apartment 190m with open landscape view',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'purpose' => $ar ? 'بيع' : 'Sale',
                'price' => 'EGP 5,600,000',
                'beds' => 3, 'baths' => 3, 'size' => 190,
                'ref' => 'XH-1009',
                'image' => '/images/demo/property-9.jpg',
            ],
        ];
    }

    /**
     * تفاصيل صفحة العقار — وصف ومميزات ومعرض صور، مفهرسة بالكود.
     * محتوى قالب للعرض؛ بيتغيّر من /admin/properties لكل وحدة حقيقية.
     */
    public static function propertyDetails(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            'XH-1001' => [
                'desc' => $ar
                    ? "شقة أرضي 165م² بجاردن خاصة 60م² في كمبوند مغلق بالتجمع الخامس. التشطيب سوبر لوكس ومسلّمة بالمطبخ والتكييفات.\nالمساحة موزّعة على ٣ غرف نوم بينهم ماستر، وريسبشن ٣ قطع على الجاردن مباشرة."
                    : "Ground-floor apartment of 165m² with a private 60m² garden inside a gated compound in Fifth Settlement. Super-lux finishing, delivered with kitchen and ACs.\nLaid out as 3 bedrooms including a master, and a 3-piece reception opening directly onto the garden.",
                'features' => $ar
                    ? "جاردن خاصة 60م²\nتشطيب سوبر لوكس بالمطبخ والتكييفات\nغرفة ماستر بحمام خاص\nجراج مغطى\nأمن ٢٤ ساعة\nنادي اجتماعي وحمام سباحة"
                    : "Private 60m² garden\nSuper-lux finishing with kitchen and ACs\nMaster bedroom with en-suite\nCovered parking\n24/7 security\nSocial club and swimming pool",
                'gallery' => ['/images/demo/property-1.jpg', '/images/demo/compound-1.jpg', '/images/demo/area-1.jpg'],
            ],
            'XH-1002' => [
                'desc' => $ar
                    ? "فيلا مستقلة 420م² مباني على أرض 600م² في العاصمة الإدارية، تشطيب كامل وجاهزة للسكن.\nدورين وروف، بحديقة محيطة من ٣ جهات وحمام سباحة خاص."
                    : "Standalone villa of 420m² built on a 600m² plot in the New Capital, fully finished and ready to move in.\nTwo floors plus a roof, surrounded by garden on three sides with a private pool.",
                'features' => $ar
                    ? "أرض 600م² وحديقة محيطة\nحمام سباحة خاص\n٥ غرف نوم بـ٥ حمامات\nغرفة خدم بمدخل مستقل\nجراج يتسع لسيارتين\nتشطيب كامل جاهز للسكن"
                    : "600m² plot with wrap-around garden\nPrivate swimming pool\n5 bedrooms with 5 bathrooms\nMaid's room with separate entrance\nTwo-car garage\nFully finished, ready to move in",
                'gallery' => ['/images/demo/property-2.jpg', '/images/demo/compound-2.jpg', '/images/demo/area-2.jpg'],
            ],
            'XH-1003' => [
                'desc' => $ar
                    ? "شقة 140م² بفيو بحري مفتوح على الكورنيش بالإسكندرية، الدور السابع بأسانسيرين.\nمناسبة للسكن الدائم أو كمصيف، والعقار مسجّل وجاهز للتنازل."
                    : "A 140m² apartment with an open sea view on the Alexandria Corniche, seventh floor with two elevators.\nSuitable as a primary home or a summer place; the unit is registered and ready for transfer.",
                'features' => $ar
                    ? "فيو بحري مفتوح\nدور سابع بأسانسيرين\n٣ غرف نوم و٢ حمام\nبلكونة على الكورنيش\nعقار مسجّل وجاهز للتنازل\nقريب من المواصلات والخدمات"
                    : "Open sea view\nSeventh floor with two elevators\n3 bedrooms and 2 bathrooms\nBalcony overlooking the Corniche\nRegistered and ready for transfer\nClose to transport and services",
                'gallery' => ['/images/demo/property-3.jpg', '/images/demo/area-3.jpg'],
            ],
            'XH-1004' => [
                'desc' => $ar
                    ? "شقة مفروشة 156م² للإيجار بالتجمع الخامس، مفروشة بالكامل بالمطبخ والتكييفات والأجهزة.\nالعقد سنوي والتأمين شهرين، والمرافق مستقلة على الوحدة."
                    : "Furnished 156m² apartment for rent in Fifth Settlement, fully furnished with kitchen, ACs and appliances.\nAnnual contract with a two-month deposit; utilities are metered separately for the unit.",
                'features' => $ar
                    ? "مفروشة بالكامل بالأجهزة\n٢ غرفة نوم و٢ حمام\nمطبخ وتكييفات\nعقد سنوي وتأمين شهرين\nأمن ٢٤ ساعة\nجراج داخل الكمبوند"
                    : "Fully furnished with appliances\n2 bedrooms and 2 bathrooms\nKitchen and ACs\nAnnual contract, two-month deposit\n24/7 security\nParking inside the compound",
                'gallery' => ['/images/demo/property-4.jpg', '/images/demo/compound-3.jpg'],
            ],
            'XH-1005' => [
                'desc' => $ar
                    ? "توين هاوس 280م² في العاصمة الإدارية باستلام فوري — من غير انتظار تسليم.\n٤ غرف نوم، حديقة أمامية وخلفية، والتشطيب نص تشطيب مع إمكانية التسليم كامل بفرق سعر."
                    : "A 280m² twin house in the New Capital with immediate handover — no waiting for delivery.\n4 bedrooms, front and back gardens, semi-finished with the option to be fully finished for a price difference.",
                'features' => $ar
                    ? "استلام فوري\nحديقة أمامية وخلفية\n٤ غرف نوم و٣ حمامات\nنص تشطيب بإمكانية التشطيب الكامل\nجراج خاص\nكمبوند مغلق بأمن ٢٤ ساعة"
                    : "Immediate handover\nFront and back gardens\n4 bedrooms and 3 bathrooms\nSemi-finished, full finishing optional\nPrivate garage\nGated compound with 24/7 security",
                'gallery' => ['/images/demo/property-5.jpg', '/images/demo/compound-2.jpg'],
            ],
            'XH-1006' => [
                'desc' => $ar
                    ? "مكتب إداري 65م² في برج مرخّص إداري بالعاصمة الإدارية، الدور الرابع بفيو على المحور.\nمناسب لشركة صغيرة أو مكتب تمثيل، والترخيص الإداري بيسهّل استخراج السجل التجاري على العنوان."
                    : "A 65m² office in a licensed administrative tower in the New Capital, fourth floor overlooking the main axis.\nSuitable for a small firm or a representative office; the administrative licence makes registering a company at the address straightforward.",
                'features' => $ar
                    ? "برج مرخّص إداري\nالدور الرابع بفيو على المحور\nمساحة مفتوحة قابلة للتقسيم\nحمام داخل الوحدة\nأسانسيرات وأمن ٢٤ ساعة\nجراج للزوار"
                    : "Licensed administrative tower\nFourth floor overlooking the main axis\nOpen-plan space, partitionable\nIn-unit bathroom\nElevators and 24/7 security\nVisitor parking",
                'gallery' => ['/images/demo/property-6.jpg', '/images/demo/compound-3.jpg'],
            ],
            'XH-1007' => [
                'desc' => $ar
                    ? "دوبلكس 235م² بروف خاص 70م² في التجمع الخامس، دورين متصلين بسلم داخلي.\n٤ غرف نوم بينهم ماستر، وريسبشن واسع مناسب للعائلات الكبيرة."
                    : "A 235m² duplex with a private 70m² roof in Fifth Settlement, two connected floors with an internal staircase.\n4 bedrooms including a master, and a wide reception suited to larger families.",
                'features' => $ar
                    ? "روف خاص 70م²\nسلم داخلي بين الدورين\n٤ غرف نوم و٣ حمامات\nريسبشن واسع\nمصعد خاص بالعمارة\nقريب من المدارس والخدمات"
                    : "Private 70m² roof\nInternal staircase between floors\n4 bedrooms and 3 bathrooms\nSpacious reception\nBuilding elevator\nClose to schools and services",
                'gallery' => ['/images/demo/property-7.jpg', '/images/demo/area-1.jpg'],
            ],
            'XH-1008' => [
                'desc' => $ar
                    ? "استوديو 78م² مفروش بالكامل للإيجار بالإسكندرية، مناسب لفرد أو زوجين.\nالإيجار شامل الفرش والأجهزة، والعقد قابل للتجديد سنويًا."
                    : "A fully furnished 78m² studio for rent in Alexandria, suitable for one person or a couple.\nRent includes furniture and appliances, with an annually renewable contract.",
                'features' => $ar
                    ? "مفروش بالكامل\nمطبخ مجهّز وتكييف\nحمام كامل\nعقد سنوي قابل للتجديد\nقريب من البحر\nمدخل مستقل"
                    : "Fully furnished\nEquipped kitchen and AC\nFull bathroom\nAnnually renewable contract\nClose to the sea\nSeparate entrance",
                'gallery' => ['/images/demo/property-8.jpg', '/images/demo/area-3.jpg'],
            ],
            'XH-1009' => [
                'desc' => $ar
                    ? "شقة 190م² بفيو لاندسكيب مفتوح في كمبوند بالتجمع الخامس، الدور الثالث.\n٣ غرف نوم و٣ حمامات، ونظام سداد من المطوّر على أقساط."
                    : "A 190m² apartment with an open landscape view inside a compound in Fifth Settlement, third floor.\n3 bedrooms and 3 bathrooms, with an instalment plan from the developer.",
                'features' => $ar
                    ? "فيو لاندسكيب مفتوح\n٣ غرف نوم و٣ حمامات\nنظام سداد بالتقسيط من المطوّر\nجراج مغطى\nنادي اجتماعي وحمام سباحة\nأمن ٢٤ ساعة"
                    : "Open landscape view\n3 bedrooms and 3 bathrooms\nDeveloper instalment plan\nCovered parking\nSocial club and swimming pool\n24/7 security",
                'gallery' => ['/images/demo/property-9.jpg', '/images/demo/compound-1.jpg', '/images/demo/area-2.jpg'],
            ],
        ];
    }

    /** مميزات ومعرض صور الكمبوندات — بالترتيب اللي في compounds() */
    public static function compoundDetails(string $locale): array
    {
        $ar = $locale === 'ar';

        $sets = [
            $ar
                ? "لاند سكيب مفتوح على 120 فدان\nنادي اجتماعي وحمام سباحة\nممشى ومسارات دراجات\nمنطقة تجارية داخل الكمبوند\nأمن وحراسة ٢٤ ساعة\nمدارس ومركز طبي على بعد دقائق"
                : "Open landscape across 120 acres\nSocial club and swimming pool\nWalking and cycling tracks\nRetail area inside the compound\n24/7 security\nSchools and a medical centre minutes away",
            $ar
                ? "واجهة بحرية 800 متر\nلاجونز صناعية\nبيتش باي خاص\nمنطقة مطاعم وكافيهات\nإدارة فندقية للوحدات\nأمن ٢٤ ساعة"
                : "800m seafront\nMan-made lagoons\nPrivate beach bay\nRestaurants and cafés zone\nHotel management for units\n24/7 security",
            $ar
                ? "موقع على المحور الرئيسي\nأبراج إدارية وتجارية مرخّصة\nجراجات متعددة الأدوار\nقاعات اجتماعات مشتركة\nأنظمة إطفاء وإنذار\nصيانة وإدارة مرافق"
                : "Located on the main axis\nLicensed administrative and retail towers\nMulti-storey parking\nShared meeting rooms\nFire and alarm systems\nFacility management and maintenance",
            $ar
                ? "كثافة بنائية منخفضة\nمساحات خضراء واسعة\nنادي رياضي وسبا\nمنطقة أطفال\nطرق داخلية بمداخل مؤمّنة\nصيانة دورية للمرافق"
                : "Low building density\nWide green areas\nGym and spa\nKids' area\nInternal roads with secured gates\nRegular facility maintenance",
            $ar
                ? "بروموناد على الواجهة\nمارينا لليخوت\nمحلات ومطاعم على الممشى\nوحدات بإطلالة بحرية\nأمن ٢٤ ساعة\nإدارة وصيانة"
                : "Waterfront promenade\nYacht marina\nShops and restaurants along the walkway\nUnits with sea views\n24/7 security\nManagement and maintenance",
            $ar
                ? "مساحات خضراء تغطي أغلب المشروع\nممشى ومسارات جري\nنادي اجتماعي\nمنطقة تجارية\nمداخل مؤمّنة\nمواقف زوار"
                : "Green areas covering most of the project\nWalking and running tracks\nSocial club\nRetail area\nSecured gates\nVisitor parking",
        ];

        $galleries = [
            ['/images/demo/compound-1.jpg', '/images/demo/property-1.jpg', '/images/demo/area-1.jpg'],
            ['/images/demo/compound-2.jpg', '/images/demo/property-3.jpg', '/images/demo/area-3.jpg'],
            ['/images/demo/compound-3.jpg', '/images/demo/property-6.jpg', '/images/demo/area-2.jpg'],
            ['/images/demo/compound-1.jpg', '/images/demo/property-9.jpg'],
            ['/images/demo/compound-2.jpg', '/images/demo/property-8.jpg'],
            ['/images/demo/compound-3.jpg', '/images/demo/property-5.jpg'],
        ];

        return array_map(
            fn ($i) => ['features' => $sets[$i], 'gallery' => $galleries[$i]],
            array_keys($sets),
        );
    }

    public static function compounds(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            [
                'id' => 1,
                'desc' => $ar ? 'سكني متكامل على 120 فدان بلاند سكيب مفتوح ونادي اجتماعي.' : 'Integrated 120-acre community with open landscape and a social club.',
                'delivery' => 'Q4 2027',
                'name' => $ar ? 'النخيل هايتس' : 'Nakheel Heights',
                'developer' => $ar ? 'شركة المروج للتطوير' : 'Al Morouj Developments',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'starting' => 'EGP 5,400,000',
                'down' => '5%',
                'years' => $ar ? '8 سنوات' : '8 years',
                'new' => true,
                'image' => '/images/demo/compound-1.jpg',
            ],
            [
                'id' => 2,
                'desc' => $ar ? 'مشروع ساحلي بلاجونز صناعية وواجهة بحرية 800 متر.' : 'Coastal project with man-made lagoons and an 800m seafront.',
                'delivery' => 'Q2 2028',
                'name' => $ar ? 'لاجون باي' : 'Lagoon Bay',
                'developer' => $ar ? 'الوادي القابضة' : 'Al Wadi Holding',
                'area' => $ar ? 'الإسكندرية' : 'Alexandria',
                'starting' => 'EGP 6,000,000',
                'down' => '10%',
                'years' => $ar ? '10 سنوات' : '10 years',
                'new' => true,
                'image' => '/images/demo/compound-2.jpg',
            ],
            [
                'id' => 3,
                'desc' => $ar ? 'قلب الحي المالي — إداري وتجاري بعوائد إيجارية معلنة.' : 'Heart of the financial district — offices and retail with stated yields.',
                'delivery' => 'Q1 2027',
                'name' => $ar ? 'كابيتال سكوير' : 'Capital Square',
                'developer' => $ar ? 'بناة المستقبل' : 'Future Builders',
                'area' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'starting' => 'EGP 7,200,000',
                'down' => '5%',
                'years' => $ar ? '7 سنوات' : '7 years',
                'new' => true,
                'image' => '/images/demo/compound-3.jpg',
            ],
            [
                'id' => 4,
                'desc' => $ar ? 'وحدات سكنية بمساحات صغيرة ومتوسطة قريبة من الخدمات.' : 'Small and mid-size residential units close to services.',
                'delivery' => 'Q3 2026',
                'name' => $ar ? 'سيلين ريزيدنس' : 'Selene Residence',
                'developer' => $ar ? 'شركة المروج للتطوير' : 'Al Morouj Developments',
                'area' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'starting' => 'EGP 4,300,000',
                'down' => '10%',
                'years' => $ar ? '6 سنوات' : '6 years',
                'new' => false,
                'image' => '/images/demo/property-5.jpg',
            ],
            [
                'id' => 5,
                'desc' => $ar ? 'واجهة بحرية مباشرة بوحدات فندقية الإدارة.' : 'Direct seafront with hotel-managed units.',
                'delivery' => 'Q4 2026',
                'name' => $ar ? 'مارينا ووك' : 'Marina Walk',
                'developer' => $ar ? 'الوادي القابضة' : 'Al Wadi Holding',
                'area' => $ar ? 'الإسكندرية' : 'Alexandria',
                'starting' => 'EGP 3,850,000',
                'down' => '15%',
                'years' => $ar ? '5 سنوات' : '5 years',
                'new' => false,
                'image' => '/images/demo/property-3.jpg',
            ],
            [
                'id' => 6,
                'desc' => $ar ? 'فيلات وتاون هاوس على محور مباشر بمساحات خضراء واسعة.' : 'Villas and townhouses on a direct axis with wide green areas.',
                'delivery' => 'Q2 2029',
                'name' => $ar ? 'جرين أفينيو' : 'Green Avenue',
                'developer' => $ar ? 'بناة المستقبل' : 'Future Builders',
                'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'starting' => 'EGP 8,900,000',
                'down' => '5%',
                'years' => $ar ? '9 سنوات' : '9 years',
                'new' => false,
                'image' => '/images/demo/property-2.jpg',
            ],
        ];
    }

    /** بطاقات قسم "مناطق بنغطيها بالتفصيل" في الرئيسية */
    public static function areas(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            [
                'id' => 1,
                'name' => $ar ? 'القاهرة الجديدة' : 'New Cairo',
                'note' => $ar ? 'التجمع الخامس · الرحاب · مدينتي' : 'Fifth Settlement · Rehab · Madinaty',
                'count' => $ar ? '412 وحدة' : '412 units',
                'image' => '/images/demo/area-1.jpg',
            ],
            [
                'id' => 2,
                'name' => $ar ? 'العاصمة الإدارية' : 'New Capital',
                'note' => $ar ? 'الحي السكني R7 · R8 · الداون تاون' : 'R7 · R8 · Downtown',
                'count' => $ar ? '389 وحدة' : '389 units',
                'image' => '/images/demo/area-2.jpg',
            ],
            [
                'id' => 3,
                'name' => $ar ? 'الإسكندرية' : 'Alexandria',
                'note' => $ar ? 'سموحة · سان ستيفانو · العجمي' : 'Smouha · San Stefano · Agami',
                'count' => $ar ? '246 وحدة' : '246 units',
                'image' => '/images/demo/area-3.jpg',
            ],
        ];
    }

    /** خيارات البحث في الهيرو — الأنواع والمناطق بأعدادها */
    public static function searchOptions(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            'types' => $ar
                ? ['شقق', 'فلل', 'تاون هاوس', 'توين هاوس', 'دوبلكس', 'بنتهاوس', 'تجاري', 'عيادات', 'محلات', 'مكاتب']
                : ['Apartments', 'Villas', 'Townhouses', 'Twin houses', 'Duplexes', 'Penthouses', 'Commercial', 'Clinics', 'Shops', 'Offices'],

            'locations' => $ar
                ? ['القاهرة الجديدة', 'الشيخ زايد', 'الساحل الشمالي', 'العين السخنة', 'العاصمة الإدارية الجديدة', 'السادس من أكتوبر', 'الغردقة']
                : ['New Cairo', 'Sheikh Zayed', 'North Coast', 'Ain Sokhna', 'New Administrative Capital', '6th of October', 'Hurghada'],

            'stats' => [
                ['value' => '6000', 'suffix' => '+', 'label' => $ar ? 'عقار' : 'properties'],
                ['value' => '420',  'suffix' => '+', 'label' => $ar ? 'كمبوند' : 'compounds'],
                ['value' => '161',  'suffix' => '+', 'label' => $ar ? 'مطوّر' : 'developers'],
            ],
        ];
    }

    /** محطات الشركة في صفحة "من نحن" */
    public static function milestones(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            ['year' => '2014', 'title' => $ar ? 'المكتب الأول' : 'The first office', 'text' => $ar ? 'مكتب تسويق صغير في التجمع الخامس بفريق من أربعة أشخاص.' : 'A small marketing office in the Fifth Settlement with a team of four.'],
            ['year' => '2018', 'title' => $ar ? 'أول شراكة تطوير' : 'First development partnership', 'text' => $ar ? 'اتفاقية حصرية مع مطوّر في القاهرة الجديدة على ثلاثة مشاريع.' : 'An exclusive agreement with a New Cairo developer covering three projects.'],
            ['year' => '2021', 'title' => $ar ? 'التوسّع للعاصمة' : 'Expanding to the Capital', 'text' => $ar ? 'فرع في الحي المالي وتغطية كاملة لمشاريع العاصمة الإدارية.' : 'A branch in the financial district and full coverage of New Capital projects.'],
            ['year' => '2024', 'title' => $ar ? 'المنصة الرقمية' : 'The digital platform', 'text' => $ar ? 'إطلاق المنصة ببيانات سعر وسداد وتسليم موثّقة لكل وحدة.' : 'Launching the platform with verified price, payment and delivery data per unit.'],
        ];
    }

    /** فريق العمل في صفحة "من نحن" */
    public static function team(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            ['name' => $ar ? 'أحمد شلبي' : 'Ahmed Shalaby', 'role' => $ar ? 'الرئيس التنفيذي' : 'Chief Executive', 'image' => '/images/demo/team-1.jpg'],
            ['name' => $ar ? 'منى عبد العزيز' : 'Mona Abdelaziz', 'role' => $ar ? 'مدير المبيعات' : 'Head of Sales', 'image' => '/images/demo/team-2.jpg'],
            ['name' => $ar ? 'كريم فتحي' : 'Karim Fathy', 'role' => $ar ? 'مسؤول علاقات المطوّرين' : 'Developer Relations', 'image' => '/images/demo/team-3.jpg'],
            ['name' => $ar ? 'سارة منصور' : 'Sara Mansour', 'role' => $ar ? 'ما بعد البيع' : 'After-sales', 'image' => '/images/demo/team-4.jpg'],
        ];
    }

    /** خيارات فورم "اتصل بنا" */
    public static function contactOptions(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            'areas' => $ar
                ? ['القاهرة الجديدة', 'العاصمة الإدارية', 'الإسكندرية', 'الساحل الشمالي', 'الشيخ زايد']
                : ['New Cairo', 'New Capital', 'Alexandria', 'North Coast', 'Sheikh Zayed'],

            'budgets' => $ar
                ? ['أقل من 3 مليون', '3 – 6 مليون', '6 – 12 مليون', 'أكثر من 12 مليون']
                : ['Under 3M', '3 – 6M', '6 – 12M', 'Over 12M'],

            'steps' => [
                ['title' => $ar ? 'بنراجع طلبك' : 'We review your request', 'text' => $ar ? 'مستشار بيقرا احتياجك وميزانيتك ويجهّز قائمة مبدئية قبل ما يكلّمك.' : 'An advisor reads your needs and budget and prepares a shortlist before calling.'],
                ['title' => $ar ? 'مكالمة في ساعتين عمل' : 'A call within two working hours', 'text' => $ar ? 'مكالمة قصيرة نتأكد فيها من التفاصيل ونرشّح 3 وحدات كحد أقصى.' : 'A short call to confirm details and shortlist three units at most.'],
                ['title' => $ar ? 'معاينة مرتبة' : 'An organised viewing', 'text' => $ar ? 'بنجدول الزيارات في يوم واحد، والمواصلات علينا.' : 'We schedule the visits in one day, with transport on us.'],
            ],

            'faq' => [
                ['q' => $ar ? 'هل فيه عمولة على الشراء؟' : 'Is there a buying commission?', 'a' => $ar ? 'لأ. عمولتنا بتيجي من المطوّر، والسعر اللي بتشتري بيه هو نفس سعر المطوّر المعلن — من غير أي زيادة عليك.' : 'No. Our commission comes from the developer, and the price you pay is the developer\'s published price — with nothing added on top.'],
                ['q' => $ar ? 'ممكن أعاين قبل ما أحجز؟' : 'Can I view before booking?', 'a' => $ar ? 'أكيد. بنرتّب معاينة على الأرض لحد ثلاث وحدات في يوم واحد، والمواصلات من عندنا.' : 'Of course. We arrange on-site viewings for up to three units in one day, with transport on us.'],
                ['q' => $ar ? 'بتشتغلوا في مناطق إيه؟' : 'Which areas do you cover?', 'a' => $ar ? 'القاهرة الجديدة والعاصمة الإدارية والإسكندرية بشكل أساسي، وبنغطي الساحل الشمالي والشيخ زايد كمان.' : 'Mainly New Cairo, the New Capital and Alexandria, and we also cover the North Coast and Sheikh Zayed.'],
                ['q' => $ar ? 'بتراجعوا الأوراق ولا لأ؟' : 'Do you review the paperwork?', 'a' => $ar ? 'أي وحدة بتدخل قائمتنا بعد مراجعة التسجيل والرخصة، وبعد التعاقد محامي المنصة بيحضر معاك.' : 'Every unit enters our list after registration and licence checks, and our lawyer attends the contract with you.'],
                ['q' => $ar ? 'لو مغيّرتش رأيي بعد الطلب؟' : 'What if I change my mind after requesting?', 'a' => $ar ? 'مفيش أي التزام. الطلب مجرد بداية محادثة، وتقدر توقفها في أي وقت من غير أي رسوم.' : 'There is no obligation. The request is just the start of a conversation and you can stop it any time at no cost.'],
                ['q' => $ar ? 'بتتعاملوا مع الإيجار برضه؟' : 'Do you handle rentals too?', 'a' => $ar ? 'أيوه، سكني وإداري. اختار «إيجار» في البحث أو اكتبها في تفاصيل الطلب.' : 'Yes, residential and commercial. Pick «Rent» in the search or mention it in your request details.'],
            ],

            'offices' => [
                ['title' => $ar ? 'المقر الرئيسي' : 'Head office', 'text' => $ar ? 'التجمع الخامس، شارع التسعين الشمالي، مبنى B14 — الدور الرابع' : 'Fifth Settlement, North 90th St., Building B14 — 4th floor'],
                ['title' => $ar ? 'فرع العاصمة' : 'Capital branch', 'text' => $ar ? 'الحي المالي، برج T7' : 'Financial district, Tower T7'],
                ['title' => $ar ? 'فرع الإسكندرية' : 'Alexandria branch', 'text' => $ar ? 'سموحة، طريق الحرية' : 'Smouha, Horreya Road'],
            ],
        ];
    }
}
