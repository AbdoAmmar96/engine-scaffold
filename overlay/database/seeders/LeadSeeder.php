<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Leads\Models\Lead;

/**
 * طلبات تجريبية عشان صندوق الوارد ميطلعش فاضي في أول تثبيت.
 * idempotent — المفتاح هو رقم الموبايل.
 */
class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'محمود عبد الرحمن', 'phone' => '01001234567', 'email' => 'm.abdelrahman@example.com',
                'area' => 'القاهرة الجديدة', 'budget' => '4 – 6 مليون', 'source' => 'contact', 'status' => 'new',
                'message' => 'بدور على شقة 3 غرف في التجمع الخامس، تسليم فوري لو أمكن.',
                'days' => 0,
            ],
            [
                'name' => 'Nour Hassan', 'phone' => '01112223344', 'email' => 'nour.hassan@example.com',
                'area' => 'العاصمة الإدارية', 'budget' => '2 – 4 مليون', 'source' => 'hero', 'status' => 'contacted',
                'message' => 'مهتمة بوحدات R7 — محتاجة أعرف أنظمة السداد المتاحة.',
                'notes' => 'اتكلمنا يوم الاتنين — طلبت عروض R7 بالإيميل. متابعة الأسبوع الجاي.',
                'days' => 2,
            ],
            [
                'name' => 'أحمد صبري', 'phone' => '01223334455', 'email' => null,
                'area' => 'الساحل الشمالي', 'budget' => '6 – 10 مليون', 'source' => 'whatsapp', 'status' => 'qualified',
                'message' => 'شاليه في سيدي عبد الرحمن، يفضّل تسليم 2027.',
                'notes' => 'ميزانيته مؤكدة وجاهز يعاين. حجزنا معاينة السبت.',
                'days' => 5,
            ],
            [
                'name' => 'هدى الشناوي', 'phone' => '01098765432', 'email' => 'hoda.shenawy@example.com',
                'area' => 'الشيخ زايد', 'budget' => 'أكثر من 10 مليون', 'source' => 'contact', 'status' => 'won',
                'message' => 'فيلا في زايد الجديدة — عندي استعداد أدفع كاش لو في خصم.',
                'notes' => 'اتعاقدت على فيلا في بادية. عمولة محصّلة.',
                'days' => 12,
            ],
            [
                'name' => 'كريم يوسف', 'phone' => '01555667788', 'email' => 'k.youssef@example.com',
                'area' => 'مدينة نصر', 'budget' => 'أقل من 2 مليون', 'source' => 'phone', 'status' => 'lost',
                'message' => 'شقة للإيجار السنوي.',
                'notes' => 'الميزانية أقل من المتاح في المنطقة — رشّحنا بدائل ورفض.',
                'days' => 20,
            ],
        ];

        foreach ($rows as $row) {
            $when = now()->subDays($row['days'])->subHours(3);
            unset($row['days']);

            Lead::updateOrCreate(['phone' => $row['phone']], $row)
                ->forceFill(['created_at' => $when, 'updated_at' => $when])
                ->save();
        }

        $this->command?->info(sprintf('  طلبات: %d', Lead::count()));
    }
}
