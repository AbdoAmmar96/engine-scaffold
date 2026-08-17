<?php

namespace App\Support;

/**
 * بيانات تجريبية للعرض فقط — أسماء المشاريع خيالية والأرقام واقعية للسوق.
 * في المرحلة 4 الصفحات بتتوصل بموديلات Properties/Compounds بنفس شكل الـ props.
 */
class DemoContent
{
    public static function properties(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            ['id' => 1, 'title' => $ar ? 'شقة 165م بجاردن في التجمع الخامس' : 'Apartment 165m with garden in Fifth Settlement', 'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo', 'purpose' => $ar ? 'بيع' : 'Sale', 'price' => 'EGP 4,850,000', 'beds' => 3, 'baths' => 2, 'size' => 165, 'ref' => 'BP-R1001'],
            ['id' => 2, 'title' => $ar ? 'فيلا مستقلة 420م تشطيب كامل' : 'Standalone villa 420m fully finished', 'area' => $ar ? 'الشيخ زايد' : 'Sheikh Zayed', 'purpose' => $ar ? 'بيع' : 'Sale', 'price' => 'EGP 18,500,000', 'beds' => 5, 'baths' => 5, 'size' => 420, 'ref' => 'BP-R1002'],
            ['id' => 3, 'title' => $ar ? 'شاليه 120م متشطب على اللاجون' : 'Chalet 120m finished on lagoon', 'area' => $ar ? 'الساحل الشمالي' : 'North Coast', 'purpose' => $ar ? 'بيع' : 'Sale', 'price' => 'EGP 6,200,000', 'beds' => 2, 'baths' => 2, 'size' => 120, 'ref' => 'BP-R1003'],
            ['id' => 4, 'title' => $ar ? 'شقة مفروشة للإيجار بالمطبخ والتكييفات' : 'Furnished apartment for rent with kitchen and ACs', 'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo', 'purpose' => $ar ? 'إيجار' : 'Rent', 'price' => $ar ? 'EGP 38,000 / شهريًا' : 'EGP 38,000 / mo', 'beds' => 2, 'baths' => 2, 'size' => 156, 'ref' => 'BP-N1004'],
            ['id' => 5, 'title' => $ar ? 'توين هاوس 280م استلام فوري' : 'Twin house 280m ready to move', 'area' => $ar ? '6 أكتوبر' : '6th of October', 'purpose' => $ar ? 'بيع' : 'Sale', 'price' => 'EGP 9,750,000', 'beds' => 4, 'baths' => 3, 'size' => 280, 'ref' => 'BP-R1005'],
            ['id' => 6, 'title' => $ar ? 'عيادة 65م في مجمع طبي مرخّص' : 'Clinic 65m in licensed medical complex', 'area' => $ar ? 'العاصمة الإدارية' : 'New Capital', 'purpose' => $ar ? 'بيع' : 'Sale', 'price' => 'EGP 3,900,000', 'beds' => 0, 'baths' => 1, 'size' => 65, 'ref' => 'BP-C1006'],
        ];
    }

    public static function compounds(string $locale): array
    {
        $ar = $locale === 'ar';

        return [
            ['id' => 1, 'name' => $ar ? 'النخيل هايتس' : 'Nakheel Heights', 'developer' => $ar ? 'شركة المروج للتطوير' : 'Al Morouj Developments', 'area' => $ar ? 'القاهرة الجديدة' : 'New Cairo', 'starting' => 'EGP 5,400,000', 'down' => '5%', 'years' => $ar ? 'حتى 8 سنوات' : 'Up to 8 years', 'new' => true],
            ['id' => 2, 'name' => $ar ? 'لاجون باي' : 'Lagoon Bay', 'developer' => $ar ? 'الوادي القابضة' : 'Al Wadi Holding', 'area' => $ar ? 'الساحل الشمالي' : 'North Coast', 'starting' => 'EGP 6,000,000', 'down' => '10%', 'years' => $ar ? 'حتى 10 سنوات' : 'Up to 10 years', 'new' => true],
            ['id' => 3, 'name' => $ar ? 'جرين سكوير' : 'Green Square', 'developer' => $ar ? 'بناة المستقبل' : 'Future Builders', 'area' => $ar ? 'الشيخ زايد' : 'Sheikh Zayed', 'starting' => 'EGP 7,200,000', 'down' => '5%', 'years' => $ar ? 'حتى 7 سنوات' : 'Up to 7 years', 'new' => false],
            ['id' => 4, 'name' => $ar ? 'سيلين ريزيدنس' : 'Selene Residence', 'developer' => $ar ? 'شركة المروج للتطوير' : 'Al Morouj Developments', 'area' => $ar ? 'العين السخنة' : 'Ain Sokhna', 'starting' => 'EGP 4,300,000', 'down' => '10%', 'years' => $ar ? 'حتى 6 سنوات' : 'Up to 6 years', 'new' => false],
        ];
    }
}
